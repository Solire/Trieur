<?php

namespace Solire\Trieur\test\units\Driver;

use atoum;
use Solire\Conf\Loader;
use Solire\Trieur\Columns;

class DataTables extends atoum
{
    public function testContrustor00()
    {
        $config = Loader::load([
            'requestUrl' => 'url.url',
            'requestMethod' => 'get',
            'dom' => 'abcd',
            'itemName' => 'personne',
            'itemsName' => 'personnes',
            'itemGenre' => 'e',
        ]);

        $columns = new Columns(Loader::load([
            'nom' => [
                'filter' => true,
                'sort' => true,
                'class' => 'uppercase',
            ],
            'prenom' => [
                'filter' => true,
                'sort' => true,
            ],
            'age' => [
                'filter' => false,
                'sort' => true,
                'width' => '10px',
            ],
        ]));

        $this
            ->if($c = $this->newTestedInstance($config, $columns))
        ;

        return $c;
    }

    public function testContrustor01()
    {
        $config = Loader::load([
            'requestUrl' => 'url.url',
            'requestMethod' => 'get',
            'dom' => 'abcd',
            'itemName' => 'personne',
            'itemsName' => 'personnes',
            'itemGenre' => 'e',
            'separator' => '|',
            'config' => [
                'test' => 'ing',
            ],
            'defaultSort' => [
                0,
                'asc',
            ],
            'autoWidth' => true,
        ]);

        $columns = new Columns(Loader::load([
            'nom' => [
                'filter' => true,
                'sort' => true,
                'class' => 'uppercase',
            ],
            'prenom' => [
                'filter' => true,
                'sort' => true,
            ],
            'age' => [
                'filter' => false,
                'sort' => true,
                'width' => '10px',
            ],
        ]));

        $this
            ->if($c = $this->newTestedInstance($config, $columns))
        ;

        return $c;
    }

    public function testReturn1()
    {
        $jsColsConfig = [
            [
                'orderable' => true,
                'searchable' => true,
                'data' => 'nom',
                'name' => 'nom',
                'title' => 'nom',
                'className' => 'uppercase',
            ],
            [
                'orderable' => true,
                'searchable' => true,
                'data' => 'prenom',
                'name' => 'prenom',
                'title' => 'prenom',
            ],
            [
                'orderable' => true,
                'searchable' => false,
                'data' => 'age',
                'name' => 'age',
                'title' => 'age',
                'width' => '10px',
            ],
        ];

        $c = $this->testContrustor00();

        $this
            ->and($c->setRequest([
                'columns' => [
                    [
                        'searchable' => true,
                        'orderable' => true,
                        'search' => [
                            'value' => '',
                        ],
                    ],
                    [
                        'searchable' => true,
                        'orderable' => true,
                        'search' => [
                            'value' => 'a',
                        ],
                    ],
                    [
                        'searchable' => true,
                        'orderable' => true,
                        'search' => [
                            'value' => '',
                        ],
                    ],
                ],
                'start' => 0,
                'length' => 10,
                'order' => [
                    [
                        'column' => 0,
                        'dir' => 'ASC',
                    ],
                ],
                'search' => [
                    'value' => 'u',
                ],
            ]))
            ->array($c->getOrder())
                ->isEqualTo([
                    [
                        0, 'ASC',
                    ],
                ])
            ->array($c->getFilters())
                ->isEqualTo([
                    [
                        [
                            'prenom',
                        ],
                        [
                            'a',
                        ],
                        'Contain',
                    ],
                    [
                        [
                            'nom',
                            'prenom',
                        ],
                        'u',
                        'Contain',
                    ],
            ])
            ->integer($c->getOffset())
                ->isEqualTo(0)
            ->variable($c->getLength())
                ->isEqualTo(10)
            ->string($c->getFilterTerm())
                ->isEqualTo('u')
            ->phpArray($c->getResponse([
                [
                    'nom' => 'dubois',
                    'prenom' => 'jean',
                    'age' => '22',
                ],
                [
                    'nom' => 'duchmucl',
                    'prenom' => 'patrick',
                    'age' => '41',
                ],
            ], 2, 2))
                ->isEqualTo([
                    'data' => [
                        [
                            'nom' => 'dubois',
                            'prenom' => 'jean',
                            'age' => '22',
                        ],
                        [
                            'nom' => 'duchmucl',
                            'prenom' => 'patrick',
                            'age' => '41',
                        ],
                    ],
                    'recordsTotal' => 2,
                    'recordsFiltered' => 2,
                ])
            ->phpArray($c->getColumnFilterConfig())
                ->isEqualTo([
                    0 => [
                        'html' => 'text',
                    ],
                    1 => [
                        'html' => 'text',
                    ],
                ])
            ->phpArray($c->getJsColsConfig())
                ->isEqualTo($jsColsConfig)
            ->phpArray($c->getJsConfig())
                ->isEqualTo([
                    'processing' => true,
                    'serverSide' => true,
                    'ajax' => [
                        'url' => 'url.url',
                        'type' => 'get',
                    ],
                    'columns' => $jsColsConfig,
                    'dom' => 'abcd',
                    'language' => [
                        'emptyTable' => 'Aucun personne'
                            . ' trouvée',
                        'info' => 'personnes _START_ à  _END_ sur _TOTAL_ personnes',
                        'infoEmpty' => 'Aucun personne',
                        'infoFiltered' => '(filtre sur _MAX_ personnes)',
                        'lengthMenu' => 'Montrer _MENU_ personnes par page',
                        'paginate' => [
                            'first' => 'première page',
                            'last' => 'dernière page',
                            'next' => 'page suivante',
                            'previous' => 'page précédente',
                        ],
                        'processing' => 'Chargement',
                        'search' => 'Recherche',
                        'searchPlaceholder' => 'Recherche',
                        'thousands' => '&nbsp;',
                        'zeroRecords' => 'Aucun personne',
                    ],
                ])
        ;
    }

    public function testReturn2()
    {
        $jsColsConfig = [
            [
                'orderable' => true,
                'searchable' => true,
                'data' => 'nom',
                'name' => 'nom',
                'title' => 'nom',
                'className' => 'uppercase',
            ],
            [
                'orderable' => true,
                'searchable' => true,
                'data' => 'prenom',
                'name' => 'prenom',
                'title' => 'prenom',
            ],
            [
                'orderable' => true,
                'searchable' => false,
                'data' => 'age',
                'name' => 'age',
                'title' => 'age',
                'width' => '10px',
            ],
        ];

        $c = $this->testContrustor01();

        $this
            ->and($c->setRequest([
                'columns' => [
                    [
                        'searchable' => true,
                        'orderable' => true,
                        'search' => [
                            'value' => '',
                        ],
                    ],
                    [
                        'searchable' => true,
                        'orderable' => true,
                        'search' => [
                            'value' => 'a',
                        ],
                    ],
                    [
                        'searchable' => true,
                        'orderable' => true,
                        'search' => [
                            'value' => '',
                        ],
                    ],
                ],
                'start' => 0,
                'length' => 10,
                'order' => [
                    [
                        'column' => 0,
                        'dir' => 'ASC',
                    ],
                ],
                'search' => [
                    'value' => 'u|v',
                ],
            ]))
            ->array($c->getOrder())
                ->isEqualTo([
                    [
                        0, 'ASC',
                    ],
                ])
            ->array($c->getFilters())
                ->isEqualTo([
                    [
                        [
                            'prenom',
                        ],
                        [
                            'a',
                        ],
                        'Contain',
                    ],
                    [
                        [
                            'nom',
                            'prenom',
                        ],
                        'u|v',
                        'Contain',
                    ],
            ])
            ->integer($c->getOffset())
                ->isEqualTo(0)
            ->variable($c->getLength())
                ->isEqualTo(10)
            ->string($c->getFilterTerm())
                ->isEqualTo('u|v')
            ->phpArray($c->getResponse([
                [
                    'nom' => 'dubois',
                    'prenom' => 'jean',
                    'age' => '22',
                ],
                [
                    'nom' => 'duchmucl',
                    'prenom' => 'patrick',
                    'age' => '41',
                ],
            ], 2, 2))
                ->isEqualTo([
                    'data' => [
                        [
                            'nom' => 'dubois',
                            'prenom' => 'jean',
                            'age' => '22',
                        ],
                        [
                            'nom' => 'duchmucl',
                            'prenom' => 'patrick',
                            'age' => '41',
                        ],
                    ],
                    'recordsTotal' => 2,
                    'recordsFiltered' => 2,
                ])
            ->phpArray($c->getColumnFilterConfig())
                ->isEqualTo([
                    0 => [
                        'html' => 'text',
                    ],
                    1 => [
                        'html' => 'text',
                    ],
                ])
            ->phpArray($c->getJsConfig())
                ->isEqualTo([
                    'processing' => true,
                    'serverSide' => true,
                    'ajax' => [
                        'url' => 'url.url',
                        'type' => 'get',
                    ],
                    'columns' => $jsColsConfig,
                    'dom' => 'abcd',
                    'test' => 'ing',
                    'language' => [
                        'emptyTable' => 'Aucun personne'
                        . ' trouvée',
                        'info' => 'personnes _START_ à  _END_ sur _TOTAL_ personnes',
                        'infoEmpty' => 'Aucun personne',
                        'infoFiltered' => '(filtre sur _MAX_ personnes)',
                        'lengthMenu' => 'Montrer _MENU_ personnes par page',
                        'paginate' => [
                            'first' => 'première page',
                            'last' => 'dernière page',
                            'next' => 'page suivante',
                            'previous' => 'page précédente',
                        ],
                        'processing' => 'Chargement',
                        'search' => 'Recherche',
                        'searchPlaceholder' => 'Recherche',
                        'thousands' => '&nbsp;',
                        'zeroRecords' => 'Aucun personne',
                    ],
                    'order' => [
                      0,
                      'asc',
                    ],
                    'autoWidth' => true,
                ])
        ;
    }

    public function testReturn3()
    {
        $c = $this->testContrustor00();

        $this
            ->and($c->setRequest([]))
            ->array($c->getOrder())
                ->isEqualTo([])
            ->array($c->getFilters())
                ->isEqualTo([])
        ;
    }
}
