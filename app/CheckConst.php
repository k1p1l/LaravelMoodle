<?php

namespace App;

class CheckConst
{
    public static $systemCommand = [
        'switch', 'case', 'break', 'default', 'return'
    ];

    public static $typeVariable = [
        'int' => 'int', 'float' => 'float', 'double' => 'double',
        'string' => 'string', 'boolean' => 'boolean'
    ];

    public static $sign = [
        '+', '-', '++', '--', '*', '/', '=', '==', '===',
        '+=', '-=', '.', '*=', '/=', '>', '<', '<=', '>=',
    ];


    public static function checkVariableInArray($variable)
    {
        if (in_array($variable, self::$systemCommand)) {
            return 'System Command';
        }
        if (in_array($variable, self::$typeVariable)) {
            return 'Type Variable';
        }
        if (in_array($variable, self::$sign)) {
            return 'Sign';
        }
        if (strpos($variable, '//') !== false) {
            return 'Comment';
        }

        return false;
    }
}
