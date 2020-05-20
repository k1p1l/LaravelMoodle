<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CheckConst;
use function Faker\Provider\pt_BR\check_digit;


class CompilationController extends Controller
{
    public $leksema = [];
    public $error = [];

    public static $id = 0;
    public static $checkAfterTypeVariable = false;
    public static $checkAfterSing = false;
    public static $checkAfterValue = false;
    public static $checkAfterSwitch = false;
    public static $checkAfterCase = false;
    public static $checkAfterCaseExpression = false;

    public $useVariable = [];
    public $useVariableWithType = [];
    public $variableNow = '';
    public $switchConditionAndType = [];

    public function getCode(Request $request)
    {
        if ($request->text_code) {
            return;
        } else {
            $fileCode = file_get_contents($request->file_code);
            $this->readRowString(preg_replace("/[\t\r]++/", '', $fileCode));
        }
//        dd($request);
    }

    public function readRowString($fileCode)
    {
        for ($i = 0; $i < strlen($fileCode) - 1; $i++) {
            $temp = '';
            while ($fileCode[$i] != "\n") {
                $temp .= $fileCode[$i];
                $i++;
            }

            $tmp = explode(' ', trim($temp, ';:'));

            foreach ($tmp as $value) {
                $this->parserLeksema($value, $temp);
            }
        }
        if ($this->error) {
            $filename = 'error.txt';
            file_put_contents($filename, var_export($this->error, true));
        }

        $filename = 'leksema.txt';
        file_put_contents($filename, var_export($this->leksema, true));

        dd($this->useVariableWithType);
    }

    public function parserLeksema($leksema, $rowString)
    {
        $checkTypeLeksema = CheckConst::checkVariableInArray($leksema);

        if ($checkTypeLeksema === 'Type Variable') {
            $this->leksema += [
                'id_' . ++self::$id => [
                    'type' => 'Type variable',
                    'value' => $leksema,
                ]
            ];

            self::$checkAfterTypeVariable = true;
            $this->variableNow = $leksema;

            return;
        }

        if ($checkTypeLeksema === 'Sign') {
            $this->leksema += [
                'id_' . ++self::$id => [
                    'type' => 'Sign',
                    'value' => $leksema,
                ]
            ];

            self::$checkAfterSing = true;

            return;
        }

        if (!$checkTypeLeksema && self::$checkAfterTypeVariable) {
            foreach ($this->useVariableWithType as $item){
                if ($item['value'] === $leksema && $item['type'] != $this->variableNow)
                {
                    $this->error += [
                        'id_'.++self::$id => [
                            'exception' => 'VARIABLE <-'. $leksema .'-> ALREADY ANNOUNCED'
                        ]
                    ];
                }
                return;
            }
            $this->leksema += [
                'id_' . ++self::$id => [
                    'type' => 'Name variable',
                    'value' => $leksema,
                ]
            ];

            self::$checkAfterTypeVariable = false;
            $this->useVariable[] = $leksema;
            $this->useVariableWithType[] =
                [
                    'value' => $leksema,
                    'type' => $this->variableNow,
                ];

            return;
        }

        if (!$checkTypeLeksema && self::$checkAfterSing) {
            if ($this->checkVariableInTypeVariable($leksema, $this->variableNow)) {
                $this->leksema += [
                    'id_' . ++self::$id => [
                        'type' => 'Value',
                        'value' => $leksema,
                    ]
                ];
            } else {
                $this->error += [
                    'id_' . ++self::$id => [
                        'exception' => 'CAN`OT CONVERT VARIABLE OF TYPE <-' . $this->variableNow . '-> TO TYPE ',
                        'row' => $rowString
                    ]
                ];
            }

            self::$checkAfterSing = false;
            self::$checkAfterValue = true;

            return;
        }

        if (self::$checkAfterValue) {
            $this->replaceVariableNow($leksema);
        }

        if (!$checkTypeLeksema && self::$checkAfterValue) {
            if (in_array($leksema, $this->useVariable)) {
                $this->leksema += [
                    'id_' . ++self::$id => [
                        'type' => 'Name variable',
                        'value' => $leksema,
                    ]
                ];
            } else {
                $this->error += [
                    'id_' . ++self::$id => [
                        'exception' => 'UNKNOWN VARIABLE <-' . $leksema . '->',
                        'row' => $rowString
                    ]
                ];
            }

            self::$checkAfterValue = false;

            return;
        }

        if ($checkTypeLeksema === 'System Command' && $leksema === 'switch') {
            $this->leksema += [
                'id_' . ++self::$id => [
                    'type' => 'System command',
                    'value' => $leksema,
                ]
            ];

            self::$checkAfterSwitch = true;

            return;
        }

        if (self::$checkAfterSwitch) {
            $leksema = trim($leksema, '()');
            $this->replaceVariableNow($leksema);

            $this->leksema += [
                'id_' . ++self::$id => [
                    'type' => 'Switch condition',
                    'value' => $leksema,
                ]
            ];

            self::$checkAfterSwitch = false;

            return;
        }

        if ($checkTypeLeksema === 'System Command' && $leksema === 'case') {
            $this->leksema += [
                'id_' . ++self::$id => [
                    'type' => 'System command',
                    'value' => $leksema,
                ]
            ];

            self::$checkAfterCase = true;
            return;
        }

        if (self::$checkAfterCase) {
            $leksema = trim($leksema, '():');
            if ($this->checkVariableInTypeVariable($leksema, $this->variableNow)) {
                $this->leksema += [
                    'id_' . ++self::$id => [
                        'type' => 'Case expression',
                        'value' => $leksema,
                    ]
                ];
            } else {
                $this->error += [
                    'id_' . ++self::$id => [
                        'exception' => 'CAN`OT CONVERT VARIABLE OF TYPE <-' . $this->variableNow . '-> TO TYPE ',
                        'row' => $rowString
                    ]
                ];
            }

            self::$checkAfterCase = false;
            self::$checkAfterCaseExpression = true;

            return;
        }

        if ($checkTypeLeksema === 'System Command') {
            $this->leksema += [
                'id_' . ++self::$id => [
                    'type' => 'System command',
                    'value' => $leksema,
                ]
            ];
        }

        if ($leksema === '{') {
            $this->leksema += [
                'id_' . ++self::$id => [
                    'type' => 'Open scob',
                    'value' => $leksema
                ]
            ];
        }

        if ($leksema === '}') {
            $this->leksema += [
                'id_' . ++self::$id => [
                    'type' => 'Close scob',
                    'value' => $leksema
                ]
            ];
        }

        if ($checkTypeLeksema === 'Comment') {
            $this->leksema += [
                'id_' . ++self::$id => [
                    'type' => 'Comment',
                    'value' => $leksema
                ]
            ];
        }
    }

    public function replaceVariableNow($leksema)
    {
        foreach ($this->useVariableWithType as $item) {
            if ($item['value'] == $leksema) {
                $this->variableNow = $item['type'];
            }
        }
    }

    public function checkVariableInTypeVariable($value, $typeVariable)
    {
        if (is_numeric($value) && $typeVariable === 'int') {
            return true;
        }
        if (is_float($value) && $typeVariable === 'float') {
            return true;
        }
        if (($value === 'true' || $value === 'false') && $typeVariable === 'boolean' && !is_numeric($value)) {
            return true;
        }
        if (is_string($value) && !is_numeric($value) && $typeVariable === 'string') {
            return true;
        }

        $flag = false;

        return $flag;
    }
}
