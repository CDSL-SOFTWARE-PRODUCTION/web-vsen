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
        'canonical_product' => ['navigation' => 'Canonical products'],
        'partner' => [
            'navigation' => 'Partners',
            'singular' => 'Partner',
            'type_customer' => 'Customer',
            'type_supplier' => 'Supplier',
            'lead_time' => 'Lead time (days)',
            'reserve_ttl_days' => 'Reservation TTL (days)',
        ],
        'legal_entity' => [
            'navigation' => 'Legal entities',
            'tax_code' => 'Tax ID',
        ],
        'price_list' => [
            'navigation' => 'Price lists',
            'lines' => 'Lines',
            'items_title' => 'Price lines',
        ],
        'inventory_lot' => [
            'navigation' => 'Inventory lots',
            'warehouse' => 'Warehouse',
        ],
        'inventory_reservation' => [
            'title' => 'Reservations',
            'order_item' => 'Order line',
        ],
        'inventory_ledger' => [
            'title' => 'Ledger entries',
        ],
        'stock_transfer' => [
            'navigation' => 'Stock transfers',
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
                'title' => 'Allocated budget',
                'description' => 'Current budget baseline',
            ],
            'gap' => [
                'title' => 'Cash gap',
                'description' => 'Positive value means funding shortfall',
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
