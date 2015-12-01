<?php

namespace Solire\Trieur\test\units;

use atoum as Atoum;
use Solire\Conf\Loader\ArrayToConf;
use Solire\Trieur\Format as TestClass;

/**
 * Description of Format
 *
 * @author thansen
 */
class Format extends Atoum
{
    public function testConstruct()
    {
        $conf = new ArrayToConf([
            'nom' => [
                'format' => [],
            ],
        ]);
        $columns = new \Solire\Trieur\Columns($conf);
        $this
            ->if ($f = new TestClass($columns))
            ->exception (function () use ($f) {
                $f->format([
                    [
                        'nom' => 'solire',
                    ]
                ]);
            })
            ->hasMessage('Undefined format class for column [nom]')
        ;

        $conf = new ArrayToConf([
            'nom' => [
                'format' => [
                    'class' => 'arg',
                ],
            ],
        ]);
        $columns = new \Solire\Trieur\Columns($conf);
        $this
            ->if ($f = new TestClass($columns))
            ->exception (function () use ($f) {
                $f->format([
                    [
                        'nom' => 'solire',
                    ]
                ]);
            })
            ->hasMessage('Format class [arg] for column [nom] does not exist')
        ;

        $conf = new ArrayToConf([
            'nom' => [
                'format' => [
                    'class' => '\DateTime',
                ],
            ],
        ]);
        $columns = new \Solire\Trieur\Columns($conf);
        $this
            ->if ($f = new TestClass($columns))
            ->exception (function () use ($f) {
                $f->format([
                    [
                        'nom' => 'solire',
                    ]
                ]);
            })
            ->hasMessage('Format class [\DateTime] does not extend abstract class [\Solire\Trieur\AbstractFormat]')
        ;

        $conf = new ArrayToConf([
            'nom' => [
                'format' => [
                    'class' => 'Callback',
                    'name' => 'strtoupper',
                    'cell' => 'str'
                ],
            ],
            'prenom' => [
                'format' => [
                    'class' => 'Solire\Trieur\Format\Callback',
                    'name' => 'ucfirst',
                    'cell' => 'str'
                ],
            ],
            'age' => []
        ]);
        $columns = new \Solire\Trieur\Columns($conf);

        $this
            ->if ($f = new TestClass($columns))
            ->array ($f->format([
                    [
                        'nom' => 'solire',
                        'prenom' => 'thomas',
                    ]
                ]))
                ->isEqualTo([
                    [
                        'nom' => 'SOLIRE',
                        'prenom' => 'Thomas',
                        'age' => '',
                    ]
                ])
        ;
    }
}
