<?php

namespace App\Support;

class StudentPassword
{
    /**
     * Six-digit PIN for young Code Club students (digits 2–9 only, no 0/1).
     */
    public static function generateKidFriendly(): string
    {
        $chars = '23456789';
        $password = '';

        for ($i = 0; $i < 6; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    /**
     * Eight-character password for ICT / CodeCamp school-program students.
     */
    public static function generateSimple(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';

        for ($i = 0; $i < 8; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }
}
