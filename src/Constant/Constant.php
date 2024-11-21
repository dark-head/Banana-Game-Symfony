<?php

namespace App\Constant;

class Constant
{
    public const ROLE_ADMIN = "ROLE_ADMIN";
    public const ROLE_PLAYER = "ROLE_PLAYER";

    public const EASY_LEVEL = 101;
    public const MEDIUM_LEVEL = 102;
    public const HARD_LEVEL = 103;

    public static array $gameLevels = [
        self::EASY_LEVEL => 'EASY',
        self::MEDIUM_LEVEL => 'MEDIUM',
        self::HARD_LEVEL => 'HARD',
    ];

}