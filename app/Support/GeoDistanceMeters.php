<?php

namespace App\Support;

/**
 * Haversine distance between two points given as "lat,lng" or "lat, lng".
 */
final class GeoDistanceMeters
{
    public static function parsePair(string $raw): ?array
    {
        $parts = array_map('trim', explode(',', $raw, 2));
        if (count($parts) !== 2) {
            return null;
        }
        $lat = (float) $parts[0];
        $lng = (float) $parts[1];
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return [$lat, $lng];
    }

    public static function betweenStrings(?string $a, ?string $b): ?float
    {
        if ($a === null || $b === null || $a === '' || $b === '') {
            return null;
        }
        $pa = self::parsePair($a);
        $pb = self::parsePair($b);
        if ($pa === null || $pb === null) {
            return null;
        }

        return self::haversine($pa[0], $pa[1], $pb[0], $pb[1]);
    }

    private static function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth * $c;
    }
}
