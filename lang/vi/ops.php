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
        'payment_milestone' => ['navigation' => 'Moc thanh toan'],
        'cash_plan_event' => ['navigation' => 'Ke hoach dong tien'],
        'document' => ['navigation' => 'Chung tu'],
        'execution_issue' => ['navigation' => 'Van de thuc thi'],
        'user' => ['navigation' => 'Nguoi dung'],
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
