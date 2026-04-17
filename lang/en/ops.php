<?php

return [
    'nav_groups' => [
        'master_data' => 'Master data',
        'system' => 'System',
    ],
    'clusters' => [
        'master_data' => 'Master data',
        'inventory' => 'Inventory & warehouse',
        'finance' => 'Finance & cashflow',
        'supply' => 'Supply & procurement',
        'demand' => 'Demand & contract',
        'delivery' => 'Delivery & logistics',
    ],
    'common' => [
        'contract' => 'Contract',
        'vendor' => 'Vendor',
        'contract_item' => 'Contract item',
        'payment_milestone' => 'Payment milestone',
        'item' => 'Item',
        'milestone' => 'Milestone',
        'owner' => 'Owner',
        'status' => 'Status',
        'risk_level' => 'Risk level',
        'docs_status' => [
            'missing' => 'Missing',
            'partial' => 'Partial',
            'complete' => 'Complete',
        ],
        'risk' => [
            'green' => 'Green',
            'amber' => 'Amber',
            'red' => 'Red',
        ],
    ],
    'resources' => [
        'contract' => ['navigation' => 'Contracts'],
        'order' => ['navigation' => 'Orders'],
        'payment_milestone' => ['navigation' => 'Payment milestones'],
        'cash_plan_event' => ['navigation' => 'Cash plan events'],
        'document' => ['navigation' => 'Documents'],
        'execution_issue' => ['navigation' => 'Execution issues'],
        'audit_log' => ['navigation' => 'Audit logs'],
        'user' => ['navigation' => 'Users'],
        'delivery' => ['navigation' => 'Deliveries'],
        'invoice' => ['navigation' => 'Invoices'],
        'financial_ledger' => ['navigation' => 'Financial ledger'],
        'supply_order' => ['navigation' => 'Supply orders (inbox)'],
        'vehicle' => ['navigation' => 'Vehicles'],
        'delivery_route' => ['navigation' => 'Delivery routes'],
        'canonical_product' => [
            'navigation' => 'Canonical products',
            'model_label' => 'Canonical product',
            'plural_model_label' => 'Canonical products',
            'sku_helper' => 'Enter manually or use “Generate SKU from facets” after filling attributes below.',
            'spec_json_label' => 'Facets (free-form attributes)',
            'spec_json_helper' => 'Free key/value pairs (e.g. suture_material, needle_shape, length_cm). Used to generate a stable SKU and distinguish variants.',
            'spec_key' => 'Attribute',
            'spec_value' => 'Value',
            'spec_add' => 'Add attribute',
            'generate_sku_action' => 'Generate SKU from facets',
            'generate_sku_success' => 'SKU updated.',
            'generate_sku_empty_facets' => 'Add at least one facet key/value before generating SKU.',
            'section_media' => 'Product image (URL)',
            'tab_identity' => 'Identity',
            'tab_facets' => 'Facets & SKU',
            'tab_media' => 'Image',
            'relation_group_catalog' => 'Aliases, requirements & documents',
            'fields' => [
                'sku' => 'SKU',
                'raw_name' => 'Raw name',
                'abc_class' => 'ABC class',
                'image_url' => 'Image URL',
            ],
            'image_url_helper' => 'HTTPS link to a product photo (stored as text; not uploaded to this app).',
            'ref_price_lists_title' => 'Reference price lists (lines linked to this SKU)',
            'ref_price_list_name' => 'Price list',
            'ref_price_lists_empty_heading' => 'No reference lines yet',
            'ref_price_lists_empty_desc' => 'Link this SKU from Reference price lists → price lines (choose this canonical product on the line). Then the list appears here.',
            'filters' => [
                'abc_class' => 'ABC class',
            ],
        ],
        'partner' => [
            'navigation' => 'Partners',
            'model_label' => 'Partner',
            'plural_model_label' => 'Partners',
            'singular' => 'Partner',
            'fields' => [
                'name' => 'Name',
                'type' => 'Partner type',
                'segment' => 'Segment',
                'lead_time_days' => 'Lead time (days)',
                'reliability_note' => 'Reliability notes',
                'credit_limit' => 'Credit limit',
                'outstanding_balance_cached' => 'Outstanding balance (cached)',
                'max_overdue_days_cached' => 'Max overdue days (cached)',
            ],
            'type_customer' => 'Customer',
            'type_supplier' => 'Supplier',
            'segment_options' => [
                'Hospital' => 'Hospital',
                'Dealer' => 'Dealer',
                'Clinic' => 'Clinic',
                'Other' => 'Other',
            ],
            'lead_time' => 'Lead time (days)',
            'reserve_ttl_days' => 'Reservation TTL (days)',
            'filters' => [
                'type' => 'Partner type',
            ],
            'table' => [
                'updated_at' => 'Updated at',
            ],
        ],
        'legal_entity' => [
            'navigation' => 'Legal entities',
            'tax_code' => 'Tax ID',
        ],
        'price_list' => [
            'navigation' => 'Reference price lists',
            'model_label' => 'Reference price list',
            'plural_model_label' => 'Reference price lists',
            'lines' => 'Lines',
            'items_title' => 'Reference price lines',
            'channel_options' => [
                'Hospital' => 'Hospital channel',
                'Dealer' => 'Dealer channel',
                'Tender' => 'Tender / public procurement',
                'Retail' => 'Retail channel',
            ],
            'fields' => [
                'name' => 'List name',
                'channel' => 'Sales / pricing channel',
                'valid_from' => 'Valid from',
                'valid_to' => 'Valid to',
            ],
            'item_fields' => [
                'canonical_product_sku' => 'Canonical SKU',
                'canonical_product_sku_helper' => 'Optional: tie this price line to a master product for deviation checks and cross-links.',
                'product_name' => 'Line label (product text)',
                'unit_price' => 'Reference unit price',
                'min_qty' => 'Minimum quantity',
                'currency' => 'Currency',
            ],
            'tooltips' => [
                'name' => 'Internal name for this list (e.g. “2026 dealer list — partner X”). Used for navigation only; it does not change how order prices are calculated.',
                'channel' => 'Which commercial channel this reference list applies to (hospital, dealer, tender, retail). Stored as a code; labels are shown in your language.',
                'partner' => 'Optional: tie this list to one partner (customer/supplier). Used to pick the right reference line when linking order items.',
                'valid_from' => 'First calendar day this list is considered active for checks (e.g. C-PR-001 deviation warnings).',
                'valid_to' => 'Last calendar day this list is considered active. Leave empty if there is no end date.',
                'product_name' => 'Free-text product name on this line. Order lines can link here so the system can compare the actual unit price to this reference (warn-only gate).',
                'unit_price' => 'Reference unit price in this currency. The order line may use a different negotiated price; large deviation can trigger a warning on Confirm contract.',
                'min_qty' => 'Minimum quantity this unit price applies to (e.g. price tier).',
                'currency' => 'ISO currency code (for example: USD, EUR, JPY, VND).',
                'lines_count' => 'How many reference price lines are attached to this list.',
            ],
        ],
        'inventory_lot' => [
            'navigation' => 'Inventory lots',
            'warehouse' => 'Warehouse',
            'canonical_product' => 'Canonical product (SKU)',
            'lot_code' => 'Lot / batch code',
            'supplier_ref' => 'Supplier ref / COA ref',
            'mfg_date' => 'Mfg date',
            'expiry_date' => 'Expiry date',
        ],
        'inventory_reservation' => [
            'navigation' => 'Inventory reservations',
            'title' => 'Reservations',
            'order_item' => 'Order line',
        ],
        'inventory_ledger' => [
            'title' => 'Ledger entries',
        ],
        'stock_transfer' => [
            'navigation' => 'Stock transfers',
        ],
        'return_order' => [
            'navigation' => 'Return orders (RMA)',
            'lines' => 'Lines',
            'lines_title' => 'Return lines',
            'condition_good' => 'Good (restock)',
            'condition_defective' => 'Defective',
        ],
        'requirement' => [
            'navigation' => 'Certification requirements',
            'code' => 'Code',
            'name' => 'Name',
            'type' => 'Type',
            'plural_short' => 'Certifications',
        ],
        'product_alias' => [
            'title' => 'Aliases (matching)',
            'alias_name' => 'Alias name',
        ],
        'canonical_product_documents' => [
            'title' => 'Product-level standard documents',
            'document_type' => 'Document type',
            'document_group' => 'Document group',
            'expiry_date' => 'Expiry date',
            'file_path' => 'File path',
            'notes' => 'Notes',
            'status' => [
                'required' => 'Required',
                'optional' => 'Optional',
                'provided' => 'Provided',
            ],
        ],
        'contract_item_bridge' => [
            'canonical_product' => 'Canonical product (SKU)',
            'canonical_product_help' => 'Select a SKU to link this contract line to mandatory certifications and product-level standard documents.',
            'requirements' => 'Mandatory certifications by product',
            'product_documents' => 'Product-level standard documents',
            'transaction_documents' => 'Contract transaction documents',
            'none_selected' => 'No canonical product selected.',
            'empty_requirements' => 'No mandatory certifications configured for this product.',
            'empty_product_documents' => 'No product-level standard documents configured for this product.',
            'empty_transaction_documents' => 'No transaction documents found for this contract/line yet.',
        ],
        'tender_line_requirement' => [
            'navigation' => 'Tender line ↔ certification',
            'snapshot_line' => 'Tender snapshot line',
        ],
    ],
    'order' => [
        'section' => [
            'order_info' => 'Order info',
        ],
        'fields' => [
            'tender_snapshot' => 'Tender snapshot',
            'items_count' => 'Items',
            'state' => 'State',
            'state_helper' => 'Read-only. Use table actions (Confirm contract, etc.) — state changes go through command services.',
        ],
    ],
    'sales_touchpoint' => [
        'navigation' => 'Sales touchpoints',
        'partner' => 'Customer / partner',
    ],
    'audit_log' => [
        'fields' => [
            'actor' => 'Actor',
        ],
    ],
    'contract' => [
        'section' => [
            'contract_info' => 'Contract info',
        ],
        'columns' => [
            'open_items' => 'Open items',
            'open_issues' => 'Open issues',
            'missing_docs' => 'Missing docs',
        ],
        'filters' => [
            'overdue' => 'Overdue delivery',
        ],
    ],
    'cash_plan_event' => [
        'purpose' => [
            'pay_supplier' => 'Pay supplier',
            'customs' => 'Customs',
            'logistics' => 'Logistics',
            'internal_transfer' => 'Internal transfer',
            'other' => 'Other',
        ],
        'filters' => [
            'next_7_days' => 'Next 7 days',
            'next_14_days' => 'Next 14 days',
            'next_30_days' => 'Next 30 days',
        ],
    ],
    'payment_milestone' => [
        'columns' => [
            'days_overdue' => 'Days overdue (cached)',
        ],
        'checklist' => [
            'pending' => 'Pending',
            'partial' => 'Partial',
            'complete' => 'Complete',
        ],
        'filters' => [
            'blocked_7d' => 'Blocked in next 7 days',
        ],
        'actions' => [
            'mark_ready' => 'Mark ready',
        ],
    ],
    'gates' => [
        'payment_ready' => 'Milestone marked as payment-ready.',
        'warn_marked_ready' => 'Milestone marked ready with warning override.',
    ],
    'document' => [
        'group' => [
            'source' => 'Source docs',
            'quality_legal' => 'Quality / legal',
            'delivery_install' => 'Delivery / install',
            'acceptance_payment' => 'Acceptance / payment',
        ],
        'status' => [
            'missing' => 'Missing',
            'uploaded' => 'Uploaded',
            'validated' => 'Validated',
        ],
        'filters' => [
            'document_group' => 'Document group',
            'expiring_30d' => 'Expiring in 30 days',
        ],
        'actions' => [
            'validate' => 'Validate',
        ],
    ],
    'execution_issue' => [
        'type' => [
            'delay' => 'Delay',
            'doc_missing' => 'Doc missing',
            'quality' => 'Quality',
            'scope_change' => 'Scope change',
            'cash_gap' => 'Cash gap',
            'vendor_silence' => 'Vendor silence',
            'other' => 'Other',
        ],
        'severity' => [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
        ],
        'impact' => [
            'deadline' => 'Deadline',
            'cost' => 'Cost',
            'documents' => 'Documents',
            'quality' => 'Quality',
            'payment' => 'Payment',
        ],
        'status' => [
            'open' => 'Open',
            'in_progress' => 'In progress',
            'pending_approval' => 'Pending approval',
            'resolved' => 'Resolved',
            'cancelled' => 'Cancelled',
        ],
        'filters' => [
            'severity' => 'Severity',
            'overdue' => 'Overdue',
        ],
        'actions' => [
            'start' => 'Start',
            'request_approval' => 'Request approval',
            'resolve' => 'Resolve',
        ],
        'notifications' => [
            'pending_approval' => 'Issue moved to pending approval',
        ],
    ],
    'user' => [
        'legal_entity' => 'Legal entity',
        'section' => [
            'user_details' => 'User details',
        ],
        'role' => [
            'label' => 'Role / Permission group',
            'admin_pm' => 'Founder / Admin',
            'sale' => 'Sales team',
            'mua_hang' => 'Purchasing',
            'kho' => 'Warehouse',
            'ke_toan' => 'Accounting',
        ],
    ],
    'order_items' => [
        'unit_price' => 'Unit price (channel)',
    ],
    'contract_items' => [
        'title' => 'Contract items',
        'status' => [
            'not_ordered' => 'Not ordered',
            'vendor_confirmed' => 'Vendor confirmed',
            'inbound' => 'Inbound',
            'ready_to_ship' => 'Ready to ship',
            'delivered' => 'Delivered',
            'accepted' => 'Accepted',
        ],
        'cash_status' => [
            'not_needed' => 'Not needed',
            'upcoming' => 'Upcoming',
            'need_fund' => 'Need fund',
        ],
        'actions' => [
            'mark_red' => 'Mark red',
        ],
    ],
    'delivery' => [
        'actions' => [
            'mark_delivered' => 'Mark delivered',
        ],
        'fields' => [
            'vehicle' => 'Vehicle',
            'delivery_route' => 'Delivery route',
        ],
    ],
    'invoice' => [
        'columns' => [
            'days_overdue' => 'Days overdue',
            'code' => 'Invoice code',
        ],
        'actions' => [
            'cancel_reissue' => 'Cancel and reissue (C-FIN-002)',
        ],
        'fields' => [
            'new_total' => 'New total amount (VND)',
        ],
        'notifications' => [
            'reissued' => 'Invoice voided and replacement created.',
        ],
        'create' => [
            'subheading' => 'Uses IssueInvoiceService (C-FIN-001): requires Delivered shipment, acceptance document, and payment-milestone gate per config/ops.php.',
        ],
    ],
    'supply_order' => [
        'tabs' => [
            'all' => 'All',
            'in_progress' => 'In progress',
            'draft_open' => 'Draft / Open',
        ],
        'stats' => [
            'total' => 'All POs',
            'total_desc' => 'Supply orders in system',
            'in_progress' => 'Not received',
            'in_progress_desc' => 'Excludes status Received',
            'draft_open' => 'Draft + Open',
            'draft_open_desc' => 'Likely need purchasing action',
        ],
        'filters' => [
            'legal_entity' => 'Legal entity',
        ],
    ],
    'financial_ledger' => [
        'columns' => [
            'counterparty' => 'Counterparty / partner',
        ],
        'tabs' => [
            'all' => 'All entries',
            'inflows' => 'Inflows',
            'outflows' => 'Outflows / payables angle',
        ],
        'filters' => [
            'outflows' => 'Negative amounts',
            'inflows' => 'Positive amounts',
        ],
    ],
    'tender_snapshot' => [
        'contracts' => 'Execution contracts',
    ],
    'vehicle' => [
        'plate' => 'Plate',
        'deliveries' => 'Deliveries',
    ],
    'delivery_route' => [
        'vehicle' => 'Vehicle',
        'deliveries' => 'Deliveries',
    ],
    'issue_updates' => [
        'title' => 'Issue updates',
        'updated_by' => 'Updated by',
        'by' => 'By',
    ],
    'dashboard' => [
        'title' => 'Operations overview',
        'subheading' => 'KPI strips (grouped metrics), then trend charts, then inventory — fewer boxes, same numbers.',
        'kpi_strip' => [
            'execution_risk_heading' => 'Execution & contract risk',
            'execution_risk_description' => 'Issue backlog and traffic-light contract counts in one strip.',
            'demand_supply_heading' => 'Demand & supply',
            'demand_supply_description' => 'Open orders (for sales roles) and purchase-order workload together.',
            'milestones_liquidity_heading' => 'Milestones & liquidity (14 days)',
            'milestones_liquidity_description' => 'Blocked / ready milestones and the three liquidity reference figures.',
            'debt_ledger_heading' => 'Receivables & ledger movement',
            'debt_ledger_description' => 'Portfolio aging and ledger totals; plus 30-day inflow/outflow for admins.',
        ],
        'charts' => [
            'orders_heading' => 'Orders created (last 14 days)',
            'orders_description' => 'Daily count of new orders — demand activity over time.',
            'orders_series' => 'New orders',
            'ledger_heading' => 'Ledger inflow vs outflow (last 14 days)',
            'ledger_description' => 'Sum of positive amounts (inflow) vs absolute negative amounts (outflow) per day — same source as the financial ledger.',
            'inflow' => 'Inflow',
            'outflow' => 'Outflow',
        ],
        'sections' => [
            'execution_issues' => 'Execution issues',
            'execution_issues_desc' => 'Open work items: overdue, critical severity, and total open.',
            'execution_contracts' => 'Contract portfolio (risk)',
            'execution_contracts_desc' => 'Traffic-light counts across all contracts — drill down for detail.',
            'demand' => 'Demand (sales)',
            'demand_desc' => 'Orders that are still active in the workflow.',
            'supply' => 'Supply & procurement',
            'supply_desc' => 'Purchase orders: totals and what likely needs buyer follow-up.',
            'milestones' => 'Payment milestones',
            'milestones_desc' => 'Collection readiness and blocked milestones in the near window.',
            'finance_cash' => 'Liquidity snapshot (next 14 days)',
            'finance_cash_desc' => 'Three independent numbers: planned cash events, milestone amounts due in the window, and total allocated budget across all contracts (not a forecast reconciliation).',
            'finance_ar' => 'Receivables & ledger (portfolio)',
            'finance_ar_desc' => 'Overdue milestone exposure and ledger inflow totals — use detail screens for drill-down.',
            'finance_ledger' => 'Ledger movement (last 30 days)',
            'finance_ledger_desc' => 'Recorded inflows vs outflows in the financial ledger (audit trail, not bank balance).',
            'inventory' => 'Inventory',
        ],
    ],
    'widgets' => [
        'contracts' => [
            'red' => [
                'title' => 'Red contracts',
                'description' => 'Needs immediate intervention',
            ],
            'amber' => [
                'title' => 'Amber contracts',
                'description' => 'Monitor closely',
            ],
            'green' => [
                'title' => 'Green contracts',
                'description' => 'On track',
            ],
        ],
        'issues' => [
            'overdue' => [
                'title' => 'Overdue issues',
                'description' => 'Past ETA',
            ],
            'critical' => [
                'title' => 'Critical open issues',
                'description' => 'High-impact blockers',
            ],
            'open' => [
                'title' => 'All open issues',
                'description' => 'Open + In progress + Pending approval',
            ],
        ],
        'ar' => [
            'migration_pending' => 'Run php artisan migrate to update schema',
            'overdue_milestones' => [
                'title' => 'Overdue milestones',
                'description' => 'Milestones past due date with incomplete checklist',
            ],
            'overdue_amount' => [
                'title' => 'Overdue milestone amount',
                'description' => 'Sum of amount_planned for overdue milestones',
            ],
            'ledger_inflow' => [
                'title' => 'Total inflow (ledger)',
                'description' => 'Inflow rows in financial_ledger_entries',
            ],
        ],
        'cash' => [
            'need_14d' => [
                'title' => 'Cash need next 14 days',
                'description' => 'Total planned outgoing cash',
            ],
            'allocated' => [
                'title' => 'Allocated budget (all contracts)',
                'description' => 'Sum of allocated_budget across contracts — portfolio baseline, not tied to the 14-day window above',
            ],
            'milestones_14d' => [
                'title' => 'Milestone amounts due 14d',
                'description' => 'Sum of amount_planned for milestones in window',
            ],
        ],
        'sale' => [
            'open_orders' => 'Open orders',
            'open_orders_desc' => 'Not closed or abandoned',
        ],
        'founder' => [
            'inflow_30d' => 'Ledger inflow 30d',
            'outflow_30d' => 'Ledger outflow 30d',
        ],
        'rop' => [
            'table_title' => 'ROP — lots below threshold (C-INV-004)',
            'model_label' => 'Lot',
            'plural_model_label' => 'Lots',
            'col_item' => 'Item',
            'col_wh' => 'Warehouse',
            'col_qty' => 'Available qty',
            'empty' => 'No lots below configured ROP threshold.',
        ],
        'milestones' => [
            'blocked_7d' => [
                'title' => 'Blocked milestones 7d',
                'description' => 'Due soon but checklist incomplete',
            ],
            'blocked_30d' => [
                'title' => 'Blocked milestones 30d',
                'description' => 'Upcoming payment risk',
            ],
            'ready' => [
                'title' => 'Payment ready',
                'description' => 'Milestones ready to collect',
            ],
        ],
    ],
];
