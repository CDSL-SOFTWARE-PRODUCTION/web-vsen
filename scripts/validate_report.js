const fs = require('node:fs');
const path = require('node:path');
const yaml = require('js-yaml');

const MODEL_DIR = path.join(__dirname, '../model');
const REPORT_PATH = path.join(__dirname, '../doc/report.md');

/**
 * Load and parse all YAML files in the model directory.
 */
function loadModels() {
    const files = fs.readdirSync(MODEL_DIR).filter(f => f.endsWith('.yaml'));
    const combined = {
        entities: [],
        constraints: [],
        states: new Set(),
        events: [],
        transitions: [],
        roles: new Set(['Admin', 'Sale', 'Warehouse', 'Procurement', 'Logistics', 'Finance', 'Founder'])
    };

    files.forEach(file => {
        const content = yaml.load(fs.readFileSync(path.join(MODEL_DIR, file), 'utf8'));
        
        if (content.entities) {
            content.entities.forEach(e => {
                combined.entities.push(e.id);
                if (e.owner_role) combined.roles.add(e.owner_role);
            });
        }
        
        if (content.constraints) {
            content.constraints.forEach(c => combined.constraints.push(c.id));
        }

        if (content.events) {
            content.events.forEach(ev => combined.events.push(ev.id));
        }

        if (content.state_machines) {
            content.state_machines.forEach(sm => {
                (sm.states || []).forEach(s => combined.states.add(s));
                (sm.transitions || []).forEach(tx => {
                    combined.transitions.push({
                        entity: sm.entity,
                        from: tx.from,
                        to: tx.to,
                        command: tx.command,
                        text: `${tx.from} → ${tx.to}`
                    });
                });
            });
        }
    });

    return combined;
}

/**
 * Validate report coverage with Strict Mode.
 */
function validateReport() {
    console.log("🚀 STARTING STRICT REPORT VALIDATION (Anti-Hack Mode)...");
    
    const models = loadModels();
    const reportContent = fs.readFileSync(REPORT_PATH, 'utf8');
    const reportClean = reportContent.replaceAll(/<!--[\s\S]*?-->/g, ''); // Remove comments to prevent "hidden" hacks

    const results = {
        entities: checkCoverage(models.entities, reportClean, "Entity"),
        constraints: checkCoverage(models.constraints, reportClean, "Constraint"),
        events: checkCoverage(models.events, reportClean, "Event"),
        states: checkCoverage(Array.from(models.states), reportClean, "State"),
        transitions: checkTransitionCoverage(models.transitions, reportClean),
        hallucinations: detectHallucinations(models, reportClean)
    };

    printResults("Entities", results.entities);
    printResults("Constraints", results.constraints);
    printResults("Events", results.events);
    printResults("States", results.states);
    printTransitionResults(results.transitions);
    
    if (results.hallucinations.length > 0) {
        console.log(`\n❌ HALLUCINATION ERROR: Found technical IDs in report that do NOT exist in model:`);
        console.log(`   ${results.hallucinations.join(', ')}`);
    }

    const totalMissing = results.entities.missing.length + 
                        results.constraints.missing.length + 
                        results.events.missing.length + 
                        results.states.missing.length + 
                        results.transitions.missing.length;

    console.log("\n" + "=".repeat(50));
    if (totalMissing === 0 && results.hallucinations.length === 0) {
        console.log("✅ SUCCESS: Report is 100% synchronized with Business Model!");
        console.log("   - No omissions detected.");
        console.log("   - No hallucinations detected.");
        console.log("   - All transitions explained.");
    } else {
        console.log(`⚠️  FAILED: Report has ${totalMissing} missing elements and ${results.hallucinations.length} hallucinations.`);
        process.exit(1);
    }
}

function checkCoverage(items, text, type) {
    const missing = [];
    const found = [];
    items.forEach(item => {
        const regex = new RegExp(String.raw`\b${item}\b`, 'g');
        const matches = text.match(regex);
        if (matches && matches.length > 0) {
            found.push(item);
        } else {
            missing.push(item);
        }
    });
    return { found, missing, total: items.length };
}

function checkTransitionCoverage(transitions, text) {
    const missing = [];
    const found = [];
    transitions.forEach(tx => {
        // Look for the literal arrow or enough keywords nearby
        const flowRegex = new RegExp(String.raw`\b${tx.from}\b.*?\b${tx.to}\b`, 'is');
        if (flowRegex.test(text)) {
            found.push(tx);
        } else {
            missing.push(tx);
        }
    });
    return { found, missing, total: transitions.length };
}

function detectHallucinations(models, text) {
    // Patterns for IDs: C-ABC-001, UPPER_SNAKE_CASE, CamelCase
    const patterns = [
        /\bC-[A-Z]+-\d+\b/g,           // Constraints
        /\b[A-Z][a-z]+(?:[A-Z][a-z]+)+\b/g, // CamelCase (Entities)
        /\b[A-Z]+(?:_[A-Z]+)+\b/g      // UPPER_SNAKE (Events/States)
    ];

    const allModelIds = new Set([
        ...models.entities,
        ...models.constraints,
        ...Array.from(models.states),
        ...models.events
    ]);

    // Words to ignore (false positives)
    const whitelist = new Set([
        'Muasamcong', 'Sankey', 'SingleSourceOfTruth', 'EventDriven', 'ConstraintBased',
        'IntelligenceOS', 'AuditLog', 'AuditTrail', 'DataFlow', 'ABCAnalysis', 'InventoryLot',
        'OrderLifecycle', 'InvoiceLifecycle', 'InventoryLifecycle', 'Draft', 'InProgress',
        'MisaSync', 'StateMachines', 'PhasedRollout', 'ParallelRun', 'GoLive', 'ExecutiveSummary',
        'BusinessOS', 'EventSourcing', 'InventoryLedger', 'CheckDelivery', 'CheckDoc', 'CheckMisa',
        'ForceReceive', 'CronJob', 'CurrentStock', 'ReorderPoint', 'PartialDelivery', 'ReplacementOrder'
    ]);

    const detected = new Set();
    patterns.forEach(p => {
        const matches = text.match(p);
        if (matches) {
            matches.forEach(m => {
                if (!allModelIds.has(m) && !whitelist.has(m)) {
                    detected.add(m);
                }
            });
        }
    });

    return Array.from(detected);
}

function printResults(label, result) {
    const percent = ((result.found.length / result.total) * 100).toFixed(1);
    const color = result.missing.length > 0 ? "❌" : "✅";
    console.log(`\n${color} ${label} Coverage: ${percent}% (${result.found.length}/${result.total})`);
    if (result.missing.length > 0) {
        console.log(`   Missing: ${result.missing.join(', ')}`);
    }
}

function printTransitionResults(result) {
    const percent = ((result.found.length / result.total) * 100).toFixed(1);
    const color = result.missing.length > 0 ? "❌" : "✅";
    console.log(`\n${color} State Transitions Coverage: ${percent}% (${result.found.length}/${result.total})`);
    if (result.missing.length > 0) {
        console.log(`   Missing descriptions for flows:`);
        result.missing.forEach(m => console.log(`   - [${m.entity}]: ${m.from} -> ${m.to} (via ${m.command})`));
    }
}

try {
    validateReport();
} catch (err) {
    console.error("❌ Validation Failed:", err.message);
    process.exit(1);
}

