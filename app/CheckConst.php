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
        '.='
    ];

    public static function checkVariableInArray($variable)
    {
        if (in_array($variable, self::$systemCommand)) {
            return 'typeSystemCommand';
        }
        if (in_array($variable, self::$typeVariable)) {
            return 'typeVariable';
        }
        if (in_array($variable, self::$sign)) {
            return 'typeSign';
        }
        if (strpos($variable, '//') !== false) {
            return 'typeComment';
        }

        return false;
    }
}
