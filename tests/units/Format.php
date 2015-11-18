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
            'nom' => []
        ]);
        $columns = new \Solire\Trieur\Columns($conf);

        $this
            ->if ($f = new TestClass($columns))
            ->array ($f->format([
                    [
                        'nom' => 'aaa'
                    ]
                ]))
                ->isEqualTo([
                    [
                        'nom' => 'aaa'
                    ]
                ])
        ;
    }
}
