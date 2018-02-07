<?php

namespace Solire\Trieur\test\units;

use atoum;
use Solire\Conf\Loader;

/**
 * Description of Format.
 *
 * @author thansen
 */
class Format extends atoum
{
    public function testConstruct()
    {
        $conf = Loader::load([
            'nom' => [
                'format' => [],
            ],
        ]);
        $columns = new \Solire\Trieur\Columns($conf);
        $this
            ->if($f = $this->newTestedInstance($columns))
            ->exception(function () use ($f) {
                $f->format([
                    [
                        'nom' => 'solire',
                    ],
                ]);
            })
            ->hasMessage('Undefined format class for column [nom]')
        ;

        $conf = Loader::load([
            'nom' => [
                'format' => [
                    'class' => 'arg',
                ],
            ],
        ]);
        $columns = new \Solire\Trieur\Columns($conf);
        $this
            ->if($f = $this->newTestedInstance($columns))
            ->exception(function () use ($f) {
                $f->format([
                    [
                        'nom' => 'solire',
                    ],
                ]);
            })
            ->hasMessage('Format class [arg] for column [nom] does not exist')
        ;

        $conf = Loader::load([
            'nom' => [
                'format' => [
                    'class' => '\DateTime',
                ],
            ],
        ]);
        $columns = new \Solire\Trieur\Columns($conf);
        $this
            ->if($f = $this->newTestedInstance($columns))
            ->exception(function () use ($f) {
                $f->format([
                    [
                        'nom' => 'solire',
                    ],
                ]);
            })
            ->hasMessage('Format class [\DateTime] does not extend abstract class [\Solire\Trieur\AbstractFormat]')
        ;

        $conf = Loader::load([
            'nom' => [
                'format' => [
                    'class' => 'Callback',
                    'name' => 'strtoupper',
                    'cell' => 'str',
                ],
            ],
            'prenom' => [
                'format' => [
                    'class' => 'Solire\Trieur\Format\Callback',
                    'name' => 'ucfirst',
                    'cell' => 'str',
                ],
            ],
            'age' => [],
        ]);
        $columns = new \Solire\Trieur\Columns($conf);

        $this
            ->if($f = $this->newTestedInstance($columns))
            ->array($f->format([
                    [
                        'nom' => 'solire',
                        'prenom' => 'thomas',
                    ],
                ]))
                ->isEqualTo([
                    [
                        'nom' => 'SOLIRE',
                        'prenom' => 'Thomas',
                        'age' => '',
                    ],
                ])
        ;
    }
}
