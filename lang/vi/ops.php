<?php

return [
    'nav_groups' => [
        'master_data' => 'Du lieu nen',
        'system' => 'He thong',
    ],
    'clusters' => [
        'master_data' => 'Du lieu nen',
        'inventory' => 'Ton kho va kho van',
        'finance' => 'Tai chinh va dong tien',
        'supply' => 'Cung ung va mua sam',
        'demand' => 'Nhu cau va hop dong',
        'delivery' => 'Giao hang va logistics',
    ],
    'common' => [
        'contract' => 'Hop dong',
        'vendor' => 'Nha cung cap',
        'contract_item' => 'Hang muc hop dong',
        'payment_milestone' => 'Moc thanh toan',
        'item' => 'Hang muc',
        'milestone' => 'Moc',
        'owner' => 'Nguoi phu trach',
        'status' => 'Trang thai',
        'risk_level' => 'Muc do rui ro',
        'docs_status' => [
            'missing' => 'Thieu',
            'partial' => 'Mot phan',
            'complete' => 'Day du',
        ],
        'risk' => [
            'green' => 'Xanh',
            'amber' => 'Vang',
            'red' => 'Do',
        ],
    ],
    'resources' => [
        'contract' => ['navigation' => 'Hop dong'],
        'order' => ['navigation' => 'Don hang'],
        'payment_milestone' => ['navigation' => 'Moc thanh toan'],
        'cash_plan_event' => ['navigation' => 'Ke hoach dong tien'],
        'document' => ['navigation' => 'Chung tu'],
        'execution_issue' => ['navigation' => 'Van de thuc thi'],
        'audit_log' => ['navigation' => 'Nhat ky audit'],
        'user' => ['navigation' => 'Nguoi dung'],
        'delivery' => ['navigation' => 'Chuyen giao hang'],
        'invoice' => ['navigation' => 'Hoa don'],
        'financial_ledger' => ['navigation' => 'So cai dong tien'],
    ],
    'order' => [
        'section' => [
            'order_info' => 'Thong tin don hang',
        ],
        'fields' => [
            'tender_snapshot' => 'Tender snapshot',
            'items_count' => 'So hang muc',
            'state' => 'Trang thai',
            'state_helper' => 'Chi doc. Dung nut tren bang (Confirm contract, ...) — doi state qua lenh/command, khong sua tay.',
        ],
    ],
    'sales_touchpoint' => [
        'navigation' => 'Cham soc / tiep xuc ban hang',
        'partner' => 'Khach / doi tac',
    ],
    'audit_log' => [
        'fields' => [
            'actor' => 'Nguoi thao tac',
        ],
    ],
    'contract' => [
        'section' => [
            'contract_info' => 'Thong tin hop dong',
        ],
        'columns' => [
            'open_items' => 'Hang muc dang mo',
            'open_issues' => 'Van de dang mo',
            'missing_docs' => 'Chung tu thieu',
        ],
        'filters' => [
            'overdue' => 'Tre han giao hang',
        ],
    ],
    'cash_plan_event' => [
        'purpose' => [
            'pay_supplier' => 'Thanh toan nha cung cap',
            'customs' => 'Hai quan',
            'logistics' => 'Logistics',
            'internal_transfer' => 'Dieu chuyen noi bo',
            'other' => 'Khac',
        ],
        'filters' => [
            'next_7_days' => '7 ngay toi',
            'next_14_days' => '14 ngay toi',
            'next_30_days' => '30 ngay toi',
        ],
    ],
    'payment_milestone' => [
        'columns' => [
            'days_overdue' => 'Ngay tre (cache)',
        ],
        'checklist' => [
            'pending' => 'Cho xu ly',
            'partial' => 'Mot phan',
            'complete' => 'Hoan tat',
        ],
        'filters' => [
            'blocked_7d' => 'Bi chan trong 7 ngay toi',
        ],
        'actions' => [
            'mark_ready' => 'Danh dau san sang',
        ],
    ],
    'gates' => [
        'payment_ready' => 'Da danh dau moc san sang thanh toan.',
        'warn_marked_ready' => 'Danh dau san sang voi canh bao override.',
    ],
    'document' => [
        'group' => [
            'source' => 'Chung tu nguon',
            'quality_legal' => 'Chat luong / phap ly',
            'delivery_install' => 'Giao hang / lap dat',
            'acceptance_payment' => 'Nghiem thu / thanh toan',
        ],
        'status' => [
            'missing' => 'Thieu',
            'uploaded' => 'Da tai len',
            'validated' => 'Da xac nhan',
        ],
        'filters' => [
            'document_group' => 'Nhom chung tu',
            'expiring_30d' => 'Het han trong 30 ngay',
        ],
        'actions' => [
            'validate' => 'Xac nhan',
        ],
    ],
    'execution_issue' => [
        'type' => [
            'delay' => 'Cham tien do',
            'doc_missing' => 'Thieu chung tu',
            'quality' => 'Chat luong',
            'scope_change' => 'Thay doi pham vi',
            'cash_gap' => 'Thieu dong tien',
            'vendor_silence' => 'Nha cung cap im lang',
            'other' => 'Khac',
        ],
        'severity' => [
            'low' => 'Thap',
            'medium' => 'Trung binh',
            'high' => 'Cao',
            'critical' => 'Nghiem trong',
        ],
        'impact' => [
            'deadline' => 'Tien do',
            'cost' => 'Chi phi',
            'documents' => 'Chung tu',
            'quality' => 'Chat luong',
            'payment' => 'Thanh toan',
        ],
        'status' => [
            'open' => 'Dang mo',
            'in_progress' => 'Dang xu ly',
            'pending_approval' => 'Cho phe duyet',
            'resolved' => 'Da giai quyet',
            'cancelled' => 'Da huy',
        ],
        'filters' => [
            'severity' => 'Muc do nghiem trong',
            'overdue' => 'Qua han',
        ],
        'actions' => [
            'start' => 'Bat dau',
            'request_approval' => 'Yeu cau phe duyet',
            'resolve' => 'Giai quyet',
        ],
        'notifications' => [
            'pending_approval' => 'Van de da chuyen sang trang thai cho phe duyet',
        ],
    ],
    'user' => [
        'legal_entity' => 'Phap nhan',
        'section' => [
            'user_details' => 'Thong tin nguoi dung',
        ],
        'role' => [
            'label' => 'Vai tro / Nhom quyen',
            'admin_pm' => 'Quan tri / Dieu hanh',
            'sale' => 'Nhom kinh doanh',
            'mua_hang' => 'Mua hang',
            'kho' => 'Kho',
            'ke_toan' => 'Ke toan',
        ],
    ],
    'order_items' => [
        'unit_price' => 'Don gia (kenh)',
    ],
    'contract_items' => [
        'title' => 'Hang muc hop dong',
        'status' => [
            'not_ordered' => 'Chua dat',
            'vendor_confirmed' => 'NCC da xac nhan',
            'inbound' => 'Dang ve kho',
            'ready_to_ship' => 'San sang giao',
            'delivered' => 'Da giao',
            'accepted' => 'Da nghiem thu',
        ],
        'cash_status' => [
            'not_needed' => 'Khong can',
            'upcoming' => 'Sap den han',
            'need_fund' => 'Can cap von',
        ],
        'actions' => [
            'mark_red' => 'Danh dau do',
        ],
    ],
    'delivery' => [
        'actions' => [
            'mark_delivered' => 'Danh dau da giao',
        ],
    ],
    'invoice' => [
        'columns' => [
            'days_overdue' => 'Ngay tre',
            'code' => 'Ma hoa don',
        ],
        'actions' => [
            'cancel_reissue' => 'Huy va xuat lai (C-FIN-002)',
        ],
        'fields' => [
            'new_total' => 'Tong tien moi (VND)',
        ],
        'notifications' => [
            'reissued' => 'Da huy hoa don va tao ban thay the.',
        ],
    ],
    'issue_updates' => [
        'title' => 'Cap nhat van de',
        'updated_by' => 'Cap nhat boi',
        'by' => 'Boi',
    ],
    'widgets' => [
        'contracts' => [
            'red' => [
                'title' => 'Hop dong Do',
                'description' => 'Can xu ly ngay',
            ],
            'amber' => [
                'title' => 'Hop dong Vang',
                'description' => 'Canh bao sat',
            ],
            'green' => [
                'title' => 'Hop dong Xanh',
                'description' => 'Dung tien do',
            ],
        ],
        'issues' => [
            'overdue' => [
                'title' => 'Van de tre han',
                'description' => 'Qua han ETA',
            ],
            'critical' => [
                'title' => 'Van de nghiem trong',
                'description' => 'Anh huong cao',
            ],
            'open' => [
                'title' => 'Tong van de dang mo',
                'description' => 'Mo + Dang xu ly + Cho duyet',
            ],
        ],
        'cash' => [
            'need_14d' => [
                'title' => 'Nhu cau tien mat 14 ngay',
                'description' => 'Tong chi du kien',
            ],
            'allocated' => [
                'title' => 'Ngan sach da cap',
                'description' => 'Moc ngan sach hien tai',
            ],
            'gap' => [
                'title' => 'Khoang thieu hut tien mat',
                'description' => 'Gia tri duong = thieu nguon',
            ],
        ],
        'ar' => [
            'migration_pending' => 'Chay php artisan migrate de cap nhat schema',
            'overdue_milestones' => [
                'title' => 'Moc tre han',
                'description' => 'So moc chua hoan tat checklist sau ngay den han',
            ],
            'overdue_amount' => [
                'title' => 'Tong tien moc tre',
                'description' => 'Tong amount_planned cua moc tre (uoc tinh)',
            ],
            'ledger_inflow' => [
                'title' => 'Tong Inflow (ledger)',
                'description' => 'Ghi nhan Inflow trong financial_ledger_entries',
            ],
        ],
        'milestones' => [
            'blocked_7d' => [
                'title' => 'Moc bi chan 7 ngay',
                'description' => 'Sap den han nhung checklist chua hoan tat',
            ],
            'blocked_30d' => [
                'title' => 'Moc bi chan 30 ngay',
                'description' => 'Rui ro thanh toan sap toi',
            ],
            'ready' => [
                'title' => 'San sang thanh toan',
                'description' => 'Moc da du dieu kien thu tien',
            ],
        ],
    ],
];
