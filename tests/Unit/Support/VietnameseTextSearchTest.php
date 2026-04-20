<?php

use App\Support\Search\VietnameseTextSearch;

it('normalizes vietnamese diacritics and casing', function (): void {
    expect(VietnameseTextSearch::normalize('Đơn Vị TÍNH'))
        ->toBe('don vi tinh');
});

it('matches like patterns without vietnamese accents', function (): void {
    expect(VietnameseTextSearch::likeMatch('Khẩu trang y tế', '%khau trang%'))->toBeTrue();
    expect(VietnameseTextSearch::likeMatch('Bông băng y tế', '%khong-co%'))->toBeFalse();
});

