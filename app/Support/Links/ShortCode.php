<?php

namespace App\Support\Links;

class ShortCode
{
    protected const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const BASE = 62;

    public static function encode(int $id): string
    {
        if ($id <= 0) {
            return 'lf';
        }

        $s = '';
        $n = $id;
        while ($n > 0) {
            $s = self::ALPHABET[$n % self::BASE] . $s;
            $n = intdiv($n, self::BASE);
        }

        return $s;
    }
}
