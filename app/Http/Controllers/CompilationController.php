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
    public $switchConditionAndType = [];

    public function getCode(Request $request)
    {
        if ($request->text_code) {
            return;
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
            $filename = 'error.txt';
            file_put_contents($filename, var_export($this->error, true));
            return;
        }

        $filename = 'leksema.txt';
        file_put_contents($filename, var_export($this->leksema, true));

//        $this->convertWIQA($this->leksema);
    }


    public function parserLeksema($leksema, $rowLine)
    {
        $usedMethod = CheckConst::checkVariableInArray($leksema);
        if (method_exists($this, $usedMethod)) {
            $this->$usedMethod($leksema);
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
            if ($this->checkValueInTypeVariable($leksema, $this->variableNow)) {
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

        if (!$usedMethod && self::$checkAfterValue) {
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
                    $res = substr($rowString, strpos($rowString, '=') + 1, strlen($rowString));;
                    $res = trim($res, ' ');
                    if ($this->checkValueInTypeVariable($res, $item['type'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
