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
    ],
    'order' => [
        'section' => [
            'order_info' => 'Order info',
        ],
        'fields' => [
            'tender_snapshot' => 'Tender snapshot',
            'items_count' => 'Items',
        ],
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
    'issue_updates' => [
        'title' => 'Issue updates',
        'updated_by' => 'Updated by',
        'by' => 'By',
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
        'cash' => [
            'need_14d' => [
                'title' => 'Cash need next 14 days',
                'description' => 'Total planned outgoing cash',
            ],
            'allocated' => [
                'title' => 'Allocated budget',
                'description' => 'Current budget baseline',
            ],
            'gap' => [
                'title' => 'Cash gap',
                'description' => 'Positive value means funding shortfall',
            ],
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
