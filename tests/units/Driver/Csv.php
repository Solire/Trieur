<?php
namespace Solire\Trieur\test\units\Driver;

use \atoum as Atoum;
use Solire\Trieur\Driver\Csv as TestClass;

use Solire\Trieur\Columns;
use Solire\Conf\Conf;

class Csv extends Atoum
{
    public function testContrustor00()
    {
        $config = arrayToConf([]);

        $columns = new Columns(arrayToConf([
            'nom' => [
                'filter' => true,
                'sort' => true,
            ],
            'prenom' => [
                'filter' => true,
                'sort' => true,
            ],
        ]));

        $this
            ->if($c = new TestClass($config, $columns))
            ->and($c->setRequest([]))
            ->array($c->order())
                ->isEqualTo([])
            ->array($c->getFilterTermByColumns())
                ->isEqualTo([])
            ->variable($c->offset())
                ->isNull()
            ->variable($c->length())
                ->isNull()
            ->string($c->getFilterTerm())
                ->isEmpty()
            ->string($c->getResponse([
                [
                    'dubois',
                    'jean',
                ],
                [
                    'patrick',
                    'duchmucl',
                ],
            ]))
                ->isEqualTo(
                      'dubois,jean' . "\n"
                    . 'patrick,duchmucl' . "\n"
                )
        ;
    }
}
