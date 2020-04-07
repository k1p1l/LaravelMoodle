<?php

namespace App\Http\Controllers;


use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CompilationController extends Controller
{

    public const TYPE_VARIABLE = [
        'string' => true, 'int' => true, 'float' => true, 'double' => true, 'char' => true, 'boolean' => true
    ];

    public $leksema = [];
    public $countLine = 0;

    public function getStringCode(Request $request)
    {
        $codeText = $request->input('text_code');
        $codeFile = $request->file('file_code');

        if (!$codeFile) {
            $this->parserCode($codeText);
        } else {
            $content = \Illuminate\Support\Facades\File::get($codeFile->path());
            $this->parserCode($content);
//            $this->getCycleFor($content);
        }
    }

    public function parserCode(String $text)
    {
        dd($text);
        $text = explode("\n", $text);
        foreach ($text as $line) {
//            $line = $text[4];
            $line = explode(" ", $line);
            try {
                if (self::TYPE_VARIABLE[$line[0]]) {
                    $this->leksema += [
                        'id_' . ++$this->countLine => [
                            'variable' => $line[1],
                            'value' => explode(';', $line[3])[0],
                            'operation' => $line[2],
                            'type_variable' => $line[0]
                        ]
                    ];
                }
            } catch (\Exception $exception) {
                $exception = 'Ошибка';
            }
            if (strpos($line[0], 'System') !== false) {
                $strSystem = explode('.', $line[0]);
                $doing = explode('(', $strSystem[2]);
                $this->leksema += [
                    'id_' . ++$this->countLine => [
                        'System' => $strSystem[1],
                        'doing' => $doing[0],
                        'variable' => explode(')', $doing[1])[0]
                    ]
                ];
            }
            if (strpos($line[0], 'Scanner') !== false) {
                $this->leksema += [
                    'id_' . ++$this->countLine => [
                        'System' => $line[0],
                        'doing' => 'read',
                        'variable' => $line[1],
                    ]
                ];
            }
            if ($line[0] == 'for') {
                $this->getCycleFor($line);
            }

        }

        dd($this->leksema);
    }

    public function getCycleFor($lineCycle)
    {
        $j = 0;
        $lineCycle = implode(' ', $lineCycle);
        $condition = substr($lineCycle, 5, strlen($lineCycle) - 1);
        $lineCondition = explode(';', $condition);
//        dd($lineCondition);
        foreach ($lineCondition as $line) {
            $line = explode(' ', $line);
//            dd($line);
            try {
                if (self::TYPE_VARIABLE[$line[0]]) {
                    $this->leksema += [
                        'id_' . ++$this->countLine => [
                            'cycle_for_' . ++$j => [
                                'initialization' => [
                                    'variable' => $line[1],
                                    'value' => explode(';', $line[3])[0],
                                    'operation' => $line[2],
                                    'type_variable' => $line[0]
                                ],
                                'condition' => [],
                                'iteration' => []
                            ]
                        ]
                    ];
                }
            } catch (\Exception $exception) {
                $exception = 'Ошибка';
            }
        }
        dd($this->leksema);
    }

}
