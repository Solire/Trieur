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
            ->array($c->getOrder())
                ->isEqualTo([])
            ->array($c->getFilters())
                ->isEqualTo([])
            ->variable($c->getOffset())
                ->isNull()
            ->variable($c->getLength())
                ->isNull()
            ->phpArray($c->getFilters())
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
