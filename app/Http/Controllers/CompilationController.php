<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CheckConst;


class CompilationController extends Controller
{
    public $leksema = [];
    public $WIQA = [];
    public $error = [];
    public $typeNameValue = [];

    public static $id = 0;
    public static $checkAfterTypeVariable = false;
    public static $checkAfterSing = false;
    public static $checkAfterValue = false;
    public static $checkAfterSwitch = false;
    public static $checkAfterCase = false;
    public static $checkAfterCaseExpression = false;

    public $useVariableWithType = [];
    public $useVariable = [];
    public $variableNameWithValue = [];
    public $variableNow = '';
    public $valueNow = '';
    public $switchTypeCondition = '';

    public function getCode(Request $request)
    {

        if ($request->text_code) {
//            return;
            phpinfo();
        } else {
            $fileCode = file_get_contents($request->file_code);
            $this->readRowString(preg_replace("/[\t\r]++/", '', $fileCode));
        }
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
                $this->parserLeksema($value, trim($temp, ';:'));
            }
        }

        if ($this->error) {
            $this->writeArrayInFile($this->error, 'error.txt');
        }

        $this->writeArrayInFile($this->leksema, 'leksema.txt');

        $this->convertWIQA($this->leksema);

//        return \redirect('/resource');
    }

    public function convertWIQA(array $leksems)
    {
        $arrayLeksem = $leksems;
        $Q = 1;
        $prevQ = 0;
        $prevSwitchQ = 0;
        $flagVarType = false;
        $flagVar = false;
        $flagNotVar = false;
        $flagNot = false;
        $var = '';
        $sign = '';
        $value = '';
        $kek = '';


        $flagSwitch = false;
        $switchVariableCondition = '';
        $switchExpression = '';
        $switchCode = '';
        $flagCloseSwitch = false;

        foreach ($arrayLeksem as $key => $leksem) {
            if ($leksem['type'] === 'Type Variable') {
                $this->WIQA += [
                    'Q_' . $Q => [
                        'varType' => '(' . $leksem['value'] . ')'
                    ]
                ];
                $flagVarType = true;
                $prevQ = $Q;
                $Q++;
            }

            if ($leksem['type'] === 'System Command' && $leksem['value'] === 'switch') {
                $this->WIQA += [
                    'Q_' . $Q => [
                    ]
                ];

                $prevSwitchQ = $Q;
                $Q++;
                $flagSwitch = true;

            }

            if ($leksem['type'] === 'Switch Condition' && $flagSwitch) {
                $switchVariableCondition = $leksem['value'];
            }

            if ($leksem['type'] === 'Case expression' && $flagSwitch) {
                $switchExpression .= $leksem['value'] . "\n";
            }

            if ($leksem['type'] === 'Comment' && $flagSwitch) {
                $switchCode .= $leksem['value'] . "\n";
            }

            if ($leksem['type'] === 'Close Scob' && $flagSwitch) {
                $flagSwitch = false;
                $flagCloseSwitch = true;
            }

            if ($flagCloseSwitch) {
                $temp = explode("\n", $switchExpression);
                $tmp = explode("\n", $switchCode);
                $j = 0;
                $k = 0;
//                dd(($temp));
                for ($i = 0; $i <= count($temp); $i++) {
                    if (!empty($temp[$i])) {
                        $this->WIQA['Q_' . $prevSwitchQ] += [
                            'Q_' . $prevSwitchQ . '_' . ++$j => 'IF &' . $switchVariableCondition . '& = ' . $temp[$i] . ' THEN BEGIN',
                            'Q_' . $prevSwitchQ . '_' . $j . '_' . ++$k => $tmp[$i]

                        ];
                    } else {
                        $this->WIQA['Q_' . $prevSwitchQ] += [
                            'Q_' . $prevSwitchQ . '_' . ++$j => 'ELSE',
                            'Q_' . $prevSwitchQ . '_' . $j . '_' . ++$k => $tmp[$i]
                        ];
                        break;
                    }
                }
            }

            if ($leksem['type'] === 'Variable Name' && $flagVarType) {
                $this->WIQA['Q_' . $prevQ] += [
                    'var' => '&' . $leksem['value'] . '&'
                ];
                $flagVar = true;
                $flagVarType = false;
            }

            if ($leksem['type'] === 'Value' && $flagVar) {
                $this->WIQA['Q_' . $prevQ] += [
                    'A_1' => $leksem['value']
                ];
                $flagVar = false;
            }

            if ($leksem['type'] === 'Variable Name' && !$flagVar && !$flagVarType) {
                $var = $leksem['value'];
                $flagNotVar = true;
            }

            if (strrpos($leksem['type'], 'Sign') !== false && $flagNotVar) {
                $sign = $leksem['value'];
                $flagNot = true;
            }

            if ($leksem['type'] === 'Value' && $flagNot && $flagNotVar) {
                $value = $leksem['value'];
                $flagNot = false;
                $flagNotVar = false;
            }

            if ($var && $sign && $value) {
                $newValue = $this->findIndexWIQA($var, $sign, $value);
                $this->WIQA += [
                    'Q_' . $Q => [
                        'var' => '&' . $var . '&',
                        'A_1' => $newValue
                    ]
                ];
                $Q++;
                $value = '';
                $sign = '';
                $var = '';
            }
        }
        dd($this->WIQA);

    }

    public function findIndexWIQA($var, $sign, $value)
    {
        $valueNew = '';;
        foreach ($this->WIQA as $item) {
            $varItem = trim($item['var'], '&');
            if (trim($item['var'], '&') === $var) {
                if ($sign === '.=') {
                    $valueNew = str_replace("' '", " ", $item['A_1'] . ' ' . $value);
                }
                if ($sign === '+=') {
                    $valueNew = (int)$item['A_1'] + (int)$value;
                }
                if ($sign === '-=') {
                    $valueNew = (int)$item['A_1'] - (int)$value;
                }
                if ($sign === '*=') {
                    $valueNew = (int)$item['A_1'] * (int)$value;
                }
                if ($sign === '/=') {
                    $valueNew = (int)$item['A_1'] / (int)$value;
                }
                if ($sign === '=') {
                    $valueNew = $value;
                }

                break;
            }
        }

        return (string)$valueNew;
    }

    public function writeWIQAinFile($fileName, $sting)
    {
        fwrite($fileName, $sting);
    }

    public function parserLeksema($leksema, $rowLine)
    {
        $usedMethod = CheckConst::checkVariableInArray($leksema);
        if (method_exists($this, $usedMethod)) {
            $this->$usedMethod($leksema);
        }

        if (!$usedMethod && $leksema === '{') {
            $this->addLeksemaInArray('Open Scob', $leksema);
            return;
        }

        if (!$usedMethod && $leksema === '}') {
            $this->addLeksemaInArray('Close Scob', $leksema);
            return;
        }

        if (!$usedMethod && self::$checkAfterTypeVariable) {
            if ($this->checkUseVariableInTypeVariable($leksema, $rowLine)) {
                $this->addLeksemaInArray('Variable Name', $leksema);

                $this->useVariable[] = $leksema;
                $this->valueNow = $leksema;
                $this->useVariableWithType[] = [
                    'type' => $this->variableNow,
                    'name' => $leksema
                ];
            } else {
                $this->addErrorInArray(2, $leksema, $rowLine);
            }
            self::$checkAfterTypeVariable = false;

            return;
        }

        if (!$usedMethod && self::$checkAfterSing) {
            if ($this->checkValueInTypeVariable($leksema, $this->variableNow) || $this->replaceVariableNow($rowLine)) {
                $this->addLeksemaInArray('Value', $leksema);

                $this->variableNameWithValue[] = [
                    'name' => $this->valueNow,
                    'value' => $leksema
                ];
            } else {
                $this->addErrorInArray(1, $leksema, $rowLine);
            }

            self::$checkAfterSing = false;
            self::$checkAfterValue = true;

            return;
        }

        if (!$usedMethod && self::$checkAfterValue && !self::$checkAfterSwitch && !self::$checkAfterCase) {
            if (in_array($leksema, $this->useVariable)) {
                if ($this->checkUseVariableInTypeVariable($leksema, $rowLine))
                    $this->addLeksemaInArray('Variable Name', $leksema);
                return;
            } elseif (!in_array($leksema, $this->useVariable)) {
                $this->addErrorInArray(3, $leksema, $rowLine);
                return;
            } else {
                $this->addErrorInArray(2, $leksema, $rowLine);
            }

            self::$checkAfterValue = false;

            return;
        }

        if (!$usedMethod && self::$checkAfterSwitch) {
            $leksema = trim($leksema, '()');
            foreach ($this->useVariableWithType as $item) {
                if ($item['name'] === $leksema)
                    $this->switchTypeCondition = $item['type'];
            }

            if (empty($this->switchTypeCondition)) {
                $this->addErrorInArray(4, $leksema, $rowLine);
                return;
            }

            $this->addLeksemaInArray('Switch Condition', $leksema);

            self::$checkAfterSwitch = false;
            return;
        }

        if (!$usedMethod && self::$checkAfterCase) {
            if ($this->checkValueInTypeVariable($leksema, $this->switchTypeCondition)) {
                $this->addLeksemaInArray('Case expression', $leksema);
            } else {
                $this->addErrorInArray(1, $leksema, $rowLine);
            }

            self::$checkAfterCase = false;
            return;
        }
    }

    public function replaceVariableNow($rowString)
    {
        $variable = trim(strchr($rowString, '=', strlen($rowString)), ' .');
        $value = trim(substr($rowString, strpos($rowString, '=') + 1, strlen($rowString)), ' ');
        foreach ($this->useVariableWithType as $item) {
            if ($item['name'] === $variable) {
                return $this->checkValueInTypeVariable($value, $item['type']);
            }
        }
    }

    public function typeSystemCommand($leksema)
    {
        $this->addLeksemaInArray('System Command', $leksema);

        if ($leksema === 'switch') {
            self::$checkAfterSwitch = true;
        }
        if ($leksema === 'case') {
            self::$checkAfterCase = true;
        }
    }

    public function typeComment($leksema)
    {
        $this->addLeksemaInArray('Comment', $leksema);
        return;
    }

    public function typeVariable($leksema)
    {
        $this->addLeksemaInArray('Type Variable', $leksema);

        $this->variableNow = $leksema;
        self::$checkAfterTypeVariable = true;

        return;
    }

    public function typeSign($leksema)
    {
        $type = '';
        switch ($leksema){
            case '=':
                $type = 'Sign(equally)';
                break;
            case '+':
                $type = 'Sign(plus)';
                break;
            case '-':
                $type = 'Sign(minus)';
                break;
            case '*':
                $type = 'Sign(multiply)';
                break;
            case '/':
                $type = 'Sign(divide)';
                break;
            case '.=':
                $type = 'Sign(Сoncatenation)';
                break;
            default:
                $type = 'Sign';
                break;
        }
        $this->addLeksemaInArray($type, $leksema);

        self::$checkAfterSing = true;

        return;
    }

    public function addLeksemaInArray($type, $leksema)
    {
        $this->leksema += [
            'id_' . ++self::$id => [
                'type' => $type,
                'value' => $leksema,
            ]
        ];
    }

    public function addErrorInArray($code, $leksema, $rowString)
    {
        if ($code == 1) {
            $message = 'DO NOT CONVERT TYPE <-' . $leksema . '->';
        }
        if ($code == 2) {
            $message = 'THIS VARIABLE <-' . $leksema . '-> IS ALREADY USED';
        }
        if ($code == 3) {
            $message = 'THIS VARIABLE <-' . $leksema . '-> IS NOT INITIALIZED';
        }
        if ($code == 4) {
            $message = 'UNKNOWN VARIABLE CONDITION <-' . $leksema . '->';;
        }

        $this->error += [
            'id_' . ++self::$id => [
                'error' => $message,
                'line' => $rowString,
            ]
        ];
    }

    public function checkValueInTypeVariable($value, $typeVariable)
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

        return false;
    }

    public function checkUseVariableInTypeVariable($value, $rowString = '')
    {
        if (!in_array($value, $this->useVariable)) {
            return true;
        } else {
            foreach ($this->useVariableWithType as $item) {
                if ($item['name'] === $value) {
                    $res = substr($rowString, strpos($rowString, '=') + 1, strlen($rowString));
                    $res = trim($res, ' ');
                    if ($this->checkValueInTypeVariable($res, $item['type'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function writeArrayInFile(array $array, $fileName)
    {
        $array = serialize($array);
        file_put_contents($fileName, $array);
    }

    public static function readArrayInFile($fileName)
    {
        return unserialize(file_get_contents($fileName));
    }
}
