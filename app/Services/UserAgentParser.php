<?php

namespace App\Services;

/**
 * Minimal, dependency-free User-Agent parser - deliberately not
 * exhaustive (no third-party package, per the "no audit packages"
 * requirement). Good enough to populate browser/version/OS/device
 * columns on the audit log; unrecognised strings fall back to "Unknown"
 * rather than throwing.
 */
class UserAgentParser
{
    public static function parse(?string $userAgent): array
    {
        $ua = $userAgent ?? '';

        return [
            'browser'         => self::browser($ua),
            'browser_version' => self::browserVersion($ua),
            'os'              => self::operatingSystem($ua),
            'device'          => self::device($ua),
        ];
    }

    protected static function browser(string $ua): string
    {
        return match (true) {
            (bool) preg_match('/Edg\//i', $ua) => 'Edge',
            (bool) preg_match('/OPR\//i', $ua) => 'Opera',
            (bool) preg_match('/Chrome\//i', $ua) && !preg_match('/Chromium/i', $ua) => 'Chrome',
            (bool) preg_match('/Firefox\//i', $ua) => 'Firefox',
            (bool) preg_match('/Safari\//i', $ua) && !preg_match('/Chrome/i', $ua) => 'Safari',
            (bool) preg_match('/MSIE|Trident/i', $ua) => 'Internet Explorer',
            default => 'Unknown',
        };
    }

    protected static function browserVersion(string $ua): string
    {
        $patterns = [
            'Edg'     => '/Edg\/([\d.]+)/i',
            'OPR'     => '/OPR\/([\d.]+)/i',
            'Chrome'  => '/Chrome\/([\d.]+)/i',
            'Firefox' => '/Firefox\/([\d.]+)/i',
            'Version' => '/Version\/([\d.]+)/i', // Safari
            'MSIE'    => '/MSIE ([\d.]+)/i',
        ];

        foreach ($patterns as $marker => $pattern) {
            if (str_contains($ua, $marker) && preg_match($pattern, $ua, $m)) {
                return $m[1];
            }
        }

        return 'Unknown';
    }

    protected static function operatingSystem(string $ua): string
    {
        return match (true) {
            (bool) preg_match('/Windows NT 10/i', $ua) => 'Windows 10/11',
            (bool) preg_match('/Windows NT/i', $ua) => 'Windows',
            (bool) preg_match('/Mac OS X/i', $ua) => 'macOS',
            (bool) preg_match('/Android/i', $ua) => 'Android',
            (bool) preg_match('/iPhone|iPad|iPod/i', $ua) => 'iOS',
            (bool) preg_match('/Linux/i', $ua) => 'Linux',
            default => 'Unknown',
        };
    }

    protected static function device(string $ua): string
    {
        if (preg_match('/iPad|Tablet/i', $ua)) {
            return 'Tablet';
        }

        if (preg_match('/Mobi|Android.*Mobile|iPhone/i', $ua)) {
            return 'Mobile';
        }

        return 'Desktop';
    }
}
