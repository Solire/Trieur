<?php

namespace Solire\Trieur\test\units\Driver;

use atoum;
use Solire\Conf\Loader;
use Solire\Trieur\Columns;

class Csv extends atoum
{
    public function testContrustor00()
    {
        $config = Loader::load([]);

        $columns = new Columns(Loader::load([
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
            ->if($c = $this->newTestedInstance($config, $columns))
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
