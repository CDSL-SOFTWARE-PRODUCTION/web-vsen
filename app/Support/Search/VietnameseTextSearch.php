<?php

namespace App\Support\Search;

final class VietnameseTextSearch
{
    private const VIETNAMESE_CHAR_MAP = [
        'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
        'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
        'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
        'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
        'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
        'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
        'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
        'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
        'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
        'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
        'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
        'đ' => 'd',
    ];

    public static function normalize(string $value): string
    {
        $normalized = mb_strtolower($value, 'UTF-8');
        $normalized = strtr($normalized, self::VIETNAMESE_CHAR_MAP);

        return preg_replace('/\s+/u', ' ', trim($normalized)) ?? '';
    }

    public static function likeMatch(?string $value, ?string $pattern, string $escape = '\\'): bool
    {
        if ($value === null || $pattern === null) {
            return false;
        }

        $normalizedValue = self::normalize($value);
        $normalizedPattern = self::normalize($pattern);
        $regex = self::likePatternToRegex($normalizedPattern, $escape);

        return preg_match($regex, $normalizedValue) === 1;
    }

    private static function likePatternToRegex(string $pattern, string $escape): string
    {
        $chunks = preg_split('//u', $pattern, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $escaped = false;
        $regexBody = '';

        foreach ($chunks as $char) {
            if ($escaped) {
                $regexBody .= preg_quote($char, '/');
                $escaped = false;
                continue;
            }

            if ($char === $escape) {
                $escaped = true;
                continue;
            }

            if ($char === '%') {
                $regexBody .= '.*';
                continue;
            }

            if ($char === '_') {
                $regexBody .= '.';
                continue;
            }

            $regexBody .= preg_quote($char, '/');
        }

        if ($escaped) {
            $regexBody .= preg_quote($escape, '/');
        }

        return '/^'.$regexBody.'$/u';
    }
}

