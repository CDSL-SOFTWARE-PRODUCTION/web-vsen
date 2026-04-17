<?php

return [
    'nav_groups' => [
        'master_data' => 'Dữ liệu nền',
        'system' => 'He thong',
    ],
    'clusters' => [
        'master_data' => 'Dữ liệu nền',
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
        'supply_order' => ['navigation' => 'Don mua hang (inbox)'],
        'vehicle' => ['navigation' => 'Xe'],
        'delivery_route' => ['navigation' => 'Tuyen giao hang'],
        'canonical_product' => [
            'navigation' => 'Sản phẩm chuẩn hóa',
            'model_label' => 'Sản phẩm chuẩn hóa',
            'plural_model_label' => 'Sản phẩm chuẩn hóa',
            'sku_helper' => 'Có thể nhập tay hoặc bấm “Sinh SKU từ đặc tính” sau khi điền facet bên dưới.',
            'spec_json_label' => 'Đặc tính (facet)',
            'spec_json_helper' => 'Tự do key/value (ví dụ: vật_liệu_chỉ, loại_kim, độ_dài_cm…). Dùng để sinh SKU ổn định và phân biệt biến thể.',
            'spec_key' => 'Tên thuộc tính',
            'spec_value' => 'Giá trị',
            'spec_add' => 'Thêm thuộc tính',
            'generate_sku_action' => 'Sinh SKU từ đặc tính',
            'generate_sku_success' => 'Đã cập nhật mã SKU.',
            'generate_sku_empty_facets' => 'Thêm ít nhất một cặp tên/giá trị trong Đặc tính (facet) trước khi sinh SKU.',
            'section_media' => 'Ảnh sản phẩm (URL)',
            'tab_identity' => 'Thông tin chính',
            'tab_facets' => 'Đặc tính & SKU',
            'tab_media' => 'Ảnh',
            'relation_group_catalog' => 'Alias, chứng nhận & tài liệu',
            'fields' => [
                'sku' => 'Mã SKU',
                'raw_name' => 'Tên gốc (raw)',
                'abc_class' => 'Phân loại ABC',
                'image_url' => 'URL ảnh',
            ],
            'image_url_helper' => 'Liên kết HTTPS tới ảnh minh họa (lưu dạng chữ; app không upload file ảnh).',
            'ref_price_lists_title' => 'Bảng giá tham chiếu (dòng đã gắn SKU này)',
            'ref_price_list_name' => 'Bảng giá',
            'ref_price_lists_empty_heading' => 'Chưa có dòng giá liên kết',
            'ref_price_lists_empty_desc' => 'Gắn SKU tại Bảng giá tham chiếu → dòng giá (chọn sản phẩm chuẩn hóa). Sau đó bảng sẽ hiện ở đây.',
            'filters' => [
                'abc_class' => 'Phân loại ABC',
            ],
        ],
        'partner' => [
            'navigation' => 'Đối tác',
            'model_label' => 'Đối tác',
            'plural_model_label' => 'Đối tác',
            'singular' => 'Đối tác',
            'fields' => [
                'name' => 'Tên gọi',
                'type' => 'Loại đối tác',
                'segment' => 'Phân khúc',
                'lead_time_days' => 'Thời gian giao hàng (ngày)',
                'reliability_note' => 'Ghi chú độ tin cậy',
                'credit_limit' => 'Hạn mức tín dụng',
                'outstanding_balance_cached' => 'Công nợ hiện tại (cache)',
                'max_overdue_days_cached' => 'Số ngày quá hạn tối đa (cache)',
            ],
            'type_customer' => 'Khách hàng',
            'type_supplier' => 'Nhà cung cấp',
            'segment_options' => [
                'Hospital' => 'Bệnh viện',
                'Dealer' => 'Đại lý',
                'Clinic' => 'Phòng khám',
                'Other' => 'Khác',
            ],
            'lead_time' => 'Thời gian giao (ngày)',
            'reserve_ttl_days' => 'Thời gian giữ chỗ tồn kho (ngày)',
            'filters' => [
                'type' => 'Loại đối tác',
            ],
            'table' => [
                'updated_at' => 'Cập nhật lúc',
            ],
        ],
        'legal_entity' => [
            'navigation' => 'Phap nhan',
            'tax_code' => 'MST',
        ],
        'price_list' => [
            'navigation' => 'Bảng giá tham chiếu',
            'model_label' => 'Bảng giá tham chiếu',
            'plural_model_label' => 'Bảng giá tham chiếu',
            'lines' => 'Số dòng giá',
            'items_title' => 'Dòng giá tham chiếu',
            'channel_options' => [
                'Hospital' => 'Kênh bệnh viện',
                'Dealer' => 'Kênh đại lý',
                'Tender' => 'Kênh đấu thầu / mua sắm công',
                'Retail' => 'Kênh bán lẻ',
            ],
            'fields' => [
                'name' => 'Tên gọi bảng giá',
                'channel' => 'Kênh bán / kênh áp dụng',
                'valid_from' => 'Hiệu lực từ',
                'valid_to' => 'Hiệu lực đến',
            ],
            'item_fields' => [
                'canonical_product_sku' => 'SKU chuẩn hóa',
                'canonical_product_sku_helper' => 'Tùy chọn: gắn dòng giá với sản phẩm master (kiểm tra lệch giá, liên kết hai chiều).',
                'product_name' => 'Tên dòng hàng (mô tả)',
                'unit_price' => 'Đơn giá tham chiếu',
                'min_qty' => 'Số lượng tối thiểu áp dụng',
                'currency' => 'Loại tiền',
            ],
            'tooltips' => [
                'name' => 'Tên nội bộ để phân biệt các bảng (ví dụ: “Bảng 2026 — đối tác X”). Chỉ dùng hiển thị và chọn nhanh; không tự tính lại giá trên đơn hàng.',
                'channel' => 'Kênh thương mại mà bảng giá tham chiếu này áp dụng. Giá trị lưu trong hệ thống là mã cố định; giao diện hiển thị đầy đủ tiếng Việt để dễ hiểu.',
                'partner' => 'Tùy chọn: gắn bảng với một đối tác (khách / nhà cung cấp). Giúp chọn đúng dòng giá tham chiếu khi liên kết với dòng đơn hàng.',
                'valid_from' => 'Ngày bắt đầu coi bảng có hiệu lực (dùng cho kiểm tra như cảnh báo lệch giá C-PR-001).',
                'valid_to' => 'Ngày cuối cùng bảng còn hiệu lực. Để trống nếu không giới hạn ngày kết thúc.',
                'product_name' => 'Tên hàng ghi trên dòng (text). Dòng đơn hàng có thể liên kết tới đây để hệ thống so sánh đơn giá thực tế với giá tham chiếu (cảnh báo, không khóa cứng).',
                'unit_price' => 'Đơn giá tham chiếu theo loại tiền. Giá thương lượng trên đơn có thể khác; lệch nhiều có thể cảnh báo khi xác nhận hợp đồng.',
                'min_qty' => 'Số lượng tối thiểu để áp dụng mức giá này (ví dụ bậc giá theo số lượng).',
                'currency' => 'Mã loại tiền theo chuẩn quốc tế (ví dụ: USD, EUR, JPY, VND).',
                'lines_count' => 'Số dòng giá tham chiếu đang gắn với bảng này.',
            ],
        ],
        'inventory_lot' => [
            'navigation' => 'Lo ton kho',
            'warehouse' => 'Kho',
            'canonical_product' => 'Sản phẩm chuẩn hóa (SKU)',
            'lot_code' => 'Số lô',
            'supplier_ref' => 'Tham chiếu NCC / COA',
            'mfg_date' => 'NSX',
            'expiry_date' => 'HSD',
        ],
        'inventory_reservation' => [
            'navigation' => 'Giu ton (tat ca)',
            'title' => 'Giu ton',
            'order_item' => 'Dong don hang',
        ],
        'inventory_ledger' => [
            'title' => 'But toan kho',
        ],
        'stock_transfer' => [
            'navigation' => 'Dieu chuyen kho',
        ],
        'return_order' => [
            'navigation' => 'Tra hang (RMA)',
            'lines' => 'Dong',
            'lines_title' => 'Chi tiet tra',
            'condition_good' => 'Tot (nhap lai)',
            'condition_defective' => 'Loi',
        ],
        'requirement' => [
            'navigation' => 'Yêu cầu chứng nhận',
            'code' => 'Mã',
            'name' => 'Tên',
            'type' => 'Loại',
            'plural_short' => 'Chứng chỉ',
        ],
        'product_alias' => [
            'title' => 'Tên gọi thay thế (khớp)',
            'alias_name' => 'Tên gọi thay thế',
        ],
        'canonical_product_documents' => [
            'title' => 'Hồ sơ chuẩn cấp sản phẩm',
            'document_type' => 'Loại giấy tờ',
            'document_group' => 'Nhóm giấy tờ',
            'expiry_date' => 'Ngày hết hạn',
            'file_path' => 'Đường dẫn tệp',
            'notes' => 'Ghi chú',
            'status' => [
                'required' => 'Bắt buộc',
                'optional' => 'Tùy chọn',
                'provided' => 'Đã có',
            ],
        ],
        'contract_item_bridge' => [
            'canonical_product' => 'Sản phẩm chuẩn hóa (SKU)',
            'canonical_product_help' => 'Chọn SKU để liên kết dòng hợp đồng với bộ chứng nhận và hồ sơ chuẩn cấp sản phẩm.',
            'requirements' => 'Chứng nhận bắt buộc theo sản phẩm',
            'product_documents' => 'Giấy tờ chuẩn cấp sản phẩm',
            'transaction_documents' => 'Giấy tờ giao dịch theo hợp đồng',
            'none_selected' => 'Chưa chọn sản phẩm chuẩn hóa.',
            'empty_requirements' => 'Sản phẩm này chưa khai báo chứng nhận bắt buộc.',
            'empty_product_documents' => 'Sản phẩm này chưa khai báo hồ sơ chuẩn.',
            'empty_transaction_documents' => 'Hợp đồng/dòng này chưa phát sinh giấy tờ giao dịch.',
        ],
        'tender_line_requirement' => [
            'navigation' => 'Dong thau <-> chung chi',
            'snapshot_line' => 'Dong tender snapshot',
        ],
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
        'fields' => [
            'vehicle' => 'Xe',
            'delivery_route' => 'Tuyen giao',
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
        'create' => [
            'subheading' => 'Dung IssueInvoiceService (C-FIN-001): can chuyen Delivered, chung tu nghiem thu, va gate moc thanh toan theo config/ops.php.',
        ],
    ],
    'supply_order' => [
        'tabs' => [
            'all' => 'Tat ca',
            'in_progress' => 'Dang xu ly',
            'draft_open' => 'Nhap / Mo',
        ],
        'stats' => [
            'total' => 'Tong don mua',
            'total_desc' => 'Supply order trong he thong',
            'in_progress' => 'Chua nhan du',
            'in_progress_desc' => 'Loai tru trang thai Received',
            'draft_open' => 'Nhap + Mo',
            'draft_open_desc' => 'Thuong can Mua hang xu ly',
        ],
        'filters' => [
            'legal_entity' => 'Phap nhan',
        ],
    ],
    'financial_ledger' => [
        'columns' => [
            'counterparty' => 'Doi tac / nguoi nhan / no',
        ],
        'tabs' => [
            'all' => 'Tat ca but toan',
            'inflows' => 'Thu / inflow',
            'outflows' => 'Chi / goc phai tra',
        ],
        'filters' => [
            'outflows' => 'So am (chi)',
            'inflows' => 'So duong (thu)',
        ],
    ],
    'tender_snapshot' => [
        'contracts' => 'Hop dong thuc thi',
    ],
    'vehicle' => [
        'plate' => 'Bien so',
        'deliveries' => 'Chuyen giao',
    ],
    'delivery_route' => [
        'vehicle' => 'Xe',
        'deliveries' => 'Chuyen giao',
    ],
    'issue_updates' => [
        'title' => 'Cap nhat van de',
        'updated_by' => 'Cap nhat boi',
        'by' => 'Boi',
    ],
    'dashboard' => [
        'title' => 'Bảng điều khiển vận hành',
        'subheading' => 'Hàng KPI (gom nhóm), rồi biểu đồ xu hướng, rồi tồn kho — ít hộp hơn, số liệu giữ nguyên.',
        'kpi_strip' => [
            'execution_risk_heading' => 'Thực thi & rủi ro hợp đồng',
            'execution_risk_description' => 'Issue và đếm đèn đỏ/vàng/xanh trong một dải.',
            'demand_supply_heading' => 'Nhu cầu & cung ứng',
            'demand_supply_description' => 'Đơn mở (đối với role Sales) và đơn mua cùng một dải.',
            'milestones_liquidity_heading' => 'Mốc thanh toán & thanh khoản (14 ngày)',
            'milestones_liquidity_description' => 'Mốc chặn / sẵn sàng thu và ba con số thanh khoản tham chiếu.',
            'debt_ledger_heading' => 'Công nợ & biến động sổ cái',
            'debt_ledger_description' => 'Aging danh mục và tổng ledger; thêm thu/chi 30 ngày cho admin.',
        ],
        'charts' => [
            'orders_heading' => 'Đơn hàng tạo mới (14 ngày)',
            'orders_description' => 'Số đơn mới theo ngày — hoạt động nhu cầu theo thời gian.',
            'orders_series' => 'Đơn mới',
            'ledger_heading' => 'Thu / chi trên sổ cái (14 ngày)',
            'ledger_description' => 'Tổng bút dương (thu) và tổng giá trị tuyệt đối bút âm (chi) theo ngày — cùng nguồn với ledger.',
            'inflow' => 'Thu (inflow)',
            'outflow' => 'Chi (outflow)',
        ],
        'sections' => [
            'execution_issues' => 'Vấn đề thực thi',
            'execution_issues_desc' => 'Việc đang mở: quá hạn, mức nghiêm trọng, và tổng đang mở.',
            'execution_contracts' => 'Danh mục hợp đồng (rủi ro)',
            'execution_contracts_desc' => 'Đếm theo đèn giao thông trên toàn bộ hợp đồng — vào danh sách để xem chi tiết.',
            'demand' => 'Nhu cầu (kinh doanh)',
            'demand_desc' => 'Đơn hàng còn trong luồng xử lý (chưa đóng / chưa hủy).',
            'supply' => 'Cung ứng & mua hàng',
            'supply_desc' => 'Đơn mua: tổng quan và việc cần buyer xử lý.',
            'milestones' => 'Mốc thanh toán',
            'milestones_desc' => 'Sẵn sàng thu tiền và mốc bị chặn trong cửa sổ gần.',
            'finance_cash' => 'Thanh khoản (14 ngày tới)',
            'finance_cash_desc' => 'Ba con số độc lập: kế hoạch chi tiền, tổng mốc đến hạn trong cửa sổ, và tổng ngân sách đã cấp trên toàn bộ hợp đồng — không phải đối soát một dòng.',
            'finance_ar' => 'Công nợ & sổ cái (toàn danh mục)',
            'finance_ar_desc' => 'Mốc quá hạn và tổng inflow trên ledger — xem màn chi tiết để drill-down.',
            'finance_ledger' => 'Biến động sổ cái (30 ngày)',
            'finance_ledger_desc' => 'Thu/chi ghi nhận trên ledger (dấu vết kiểm toán, không phải số dư ngân hàng).',
            'inventory' => 'Tồn kho',
        ],
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
                'title' => 'Ngân sách đã cấp (mọi hợp đồng)',
                'description' => 'Tổng allocated_budget — mốc danh mục, không gắn cửa sổ 14 ngày phía trên',
            ],
            'milestones_14d' => [
                'title' => 'Tong moc den han 14 ngay',
                'description' => 'Tong amount_planned trong cua so',
            ],
        ],
        'sale' => [
            'open_orders' => 'Don dang mo',
            'open_orders_desc' => 'Chua dong hoac huy',
        ],
        'founder' => [
            'inflow_30d' => 'Inflow ledger 30 ngay',
            'outflow_30d' => 'Outflow ledger 30 ngay',
        ],
        'rop' => [
            'table_title' => 'ROP — lo duoi nguong (C-INV-004)',
            'model_label' => 'Lo hang',
            'plural_model_label' => 'Lo hang',
            'col_item' => 'Hang',
            'col_wh' => 'Kho',
            'col_qty' => 'Ton kha dung',
            'empty' => 'Khong co lo nao duoi nguong ROP cau hinh.',
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
