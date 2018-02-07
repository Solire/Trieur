<?php

namespace Solire\Trieur\test\units;

use atoum;
use Solire\Conf\Conf;
use Solire\Conf\Loader;

class Trieur extends atoum
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    public $connection = null;

    /**
     * @var string
     */
    protected $fileName = 'clients.csv';

    public function csvPath()
    {
        return TEST_TMP_DIR . DIRECTORY_SEPARATOR . $this->fileName;
    }

    public function setUp()
    {
        $handle = fopen($this->csvPath(), 'w');
        $data = [
            ['1', 'a', '3', 'thomas', '2014-02-06'],
            ['2', 'z', '2', 'thomas', '2014-02-08'],
            ['3', 'z', '2', 'jérôme', '2014-02-16'],
            ['4', 't', '5', 'julie', '2014-02-22'],
            ['5', 't', '5', 'abel', '2014-02-01'],
            ['6', 'c', '5', 'julie', '2014-02-11'],
        ];
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
    }

    public function tearDown()
    {
        unlink($this->csvPath());
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->mockGenerator->shuntParentClassCalls();

        $this->mockGenerator->orphanize('__construct');
        $pdo = new \mock\PDO();

        $this->mockGenerator->orphanize('__construct');
        $this->connection = new \mock\Doctrine\DBAL\Connection();

        $this->mockGenerator->unshuntParentClassCalls();

        return $this->connection;
    }

    public function testConstruct()
    {
        $conf = new Conf();
        $this
            ->exception(function () use ($conf) {
                $this->newTestedInstance($conf);
            })
            ->hasMessage('No class for driver class founed or given')
        ;

        $conf = Loader::load([
            'driver' => [
                'name' => 'dataTables',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ],
            ],
            'source' => [
                'name' => 'aaa',
                'conf' => [
                    'select' => 'a',
                    'from' => [
                        'name' => 'table01',
                        'alias' => 't',
                    ],
                ],
            ],
            'columns' => [],
        ]);
        $doctrineConnection = $this->getConnection();
        $this
            ->exception(function () use ($conf) {
                $this->newTestedInstance($conf, true);
            })
            ->hasMessage('No wrapper class for source class founed')
        ;

        $conf = Loader::load([
            'driver' => [
                'name' => 'dataTables',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ],
            ],
            'source' => [
                'name' => 'doctrine',
                'conf' => [
                    'select' => 'a',
                    'from' => [
                        'name' => 'table01',
                        'alias' => 't',
                    ],
                ],
            ],
            'columns' => [],
        ]);
        $doctrineConnection = $this->getConnection();
        $this
            ->object($this->newTestedInstance($conf, $doctrineConnection))
        ;

        $conf = Loader::load([
            'driver' => [
                'class' => '\Solire\Trieur\Driver\Csv',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ],
            ],
            'source' => [
                'class' => '\Solire\Trieur\Source\Doctrine',
                'conf' => [
                    'select' => 'a',
                    'from' => [
                        'name' => 'table01',
                        'alias' => 't',
                    ],
                ],
            ],
            'columns' => [],
        ]);
        $this
            ->object($this->newTestedInstance($conf, $doctrineConnection))
        ;

        $conf = Loader::load([
            'driver' => [
                'class' => '\stdClass',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ],
            ],
            'source' => [
                'class' => '\Solire\Trieur\Source\Doctrine',
                'conf' => [
                    'select' => 'a',
                    'from' => [
                        'name' => 'table01',
                        'alias' => 't',
                    ],
                ],
            ],
            'columns' => [],
        ]);
        $this
            ->exception(function () use ($conf) {
                $this->newTestedInstance($conf);
            })
            ->hasMessage('class "\stdClass" does not extend abstract class "\Solire\Trieur\Driver"')
        ;
        $conf = Loader::load([
            'driver' => [
                'class' => '\stddddClass',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ],
            ],
            'source' => [
                'class' => '\Solire\Trieur\Source\Doctrine',
                'conf' => [
                    'select' => 'a',
                    'from' => [
                        'name' => 'table01',
                        'alias' => 't',
                    ],
                ],
            ],
            'columns' => [],
        ]);
        $this
            ->exception(function () use ($conf) {
                $this->newTestedInstance($conf);
            })
            ->hasMessage('class "\stddddClass" does not exist')
        ;

        $conf = Loader::load([
            'driver' => [
                'class' => '\Solire\Trieur\Driver\Csv',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ],
            ],
            'source' => [
                'class' => '\PDO',
                'conf' => [
                    'select' => 'a',
                    'from' => [
                        'name' => 'table01',
                        'alias' => 't',
                    ],
                ],
            ],
            'columns' => [],
        ]);
        $this
            ->exception(function () use ($conf) {
                $this->newTestedInstance($conf, true);
            })
            ->hasMessage('class "\PDO" does not extend abstract class "\Solire\Trieur\Source"')
        ;

        $conf = Loader::load([
            'driver' => [
                'class' => '\Solire\Trieur\Driver\Csv',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ],
            ],
            'source' => [
                'class' => '\PDOHOHOH',
                'conf' => [
                    'select' => 'a',
                    'from' => [
                        'name' => 'table01',
                        'alias' => 't',
                    ],
                ],
            ],
            'columns' => [],
        ]);
        $this
            ->exception(function () use ($conf) {
                $this->newTestedInstance($conf, true);
            })
            ->hasMessage('class "\PDOHOHOH" does not exist')
        ;
    }

    public function testGet()
    {
        $conf = Loader::load([
            'driver' => [
                'name' => 'dataTables',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ],
            ],
            'source' => [
                'name' => 'doctrine',
                'conf' => [
                    'select' => 'a',
                    'from' => [
                        'name' => 'table01',
                        'alias' => 't',
                    ],
                ],
            ],
            'columns' => [],
        ]);
        $doctrineConnection = $this->getConnection();
        $this
            ->if($trieur = $this->newTestedInstance($conf, $doctrineConnection))
            ->object($trieur->getDriver())
                ->isInstanceOf('\Solire\Trieur\Driver\DataTables')
            ->object($trieur->getSource())
                ->isInstanceOf('\Solire\Trieur\Source\Doctrine')
            ->object($trieur->setRequest([]))
                ->isInstanceOf('\Solire\Trieur\Trieur')
        ;
    }

    public function testGetResponse()
    {
        $conf = Loader::load([
            'driver' => [
                'name' => 'csv',
            ],
            'source' => [
                'name' => 'csv',
            ],
            'columns' => [
                '4' => [
                    'format' => [
                        'class' => 'Solire\Trieur\Format\Callback',
                        'name' => [
                            '\Solire\Trieur\Example\Format',
                            'sqlTo',
                        ],
                        'cell' => 'dateSql',
                        'arguments' => [
                            'format' => 'd/m/Y',
                        ],
                    ],
                ],
            ],
        ]);
        $this
            ->if($trieur = $this->newTestedInstance($conf, $this->csvPath()))
            ->string($trieur->getResponse())
                ->isEqualTo(
                    '06/02/2014' . "\n" .
                    '08/02/2014' . "\n" .
                    '16/02/2014' . "\n" .
                    '22/02/2014' . "\n" .
                    '01/02/2014' . "\n" .
                    '11/02/2014' . "\n"
                )
        ;

        $conf = Loader::load([
            'driver' => [
                'name' => 'csv',
            ],
            'source' => [
                'name' => 'csv',
            ],
            'columns' => [
                '0' => [
                    'format' => [
                        'class' => 'Solire\Trieur\Format\Callback',
                        'name' => [
                            '\Solire\Trieur\Example\Format',
                            'serialize',
                        ],
                        'row' => 'row',
                        'cell' => 'value',
                    ],
                ],
            ],
        ]);
        $this
            ->if($trieur = $this->newTestedInstance($conf, $this->csvPath()))
            ->string($trieur->getResponse())
                ->isEqualTo(
                    '"a:5:{i:0;s:1:""1"";i:1;s:1:""a"";i:2;s:1:""3"";i:3;s:6:""thomas"";i:4;s:10:""2014-02-06"";}|1"' . "\n" .
                    '"a:5:{i:0;s:1:""2"";i:1;s:1:""z"";i:2;s:1:""2"";i:3;s:6:""thomas"";i:4;s:10:""2014-02-08"";}|2"' . "\n" .
                    '"a:5:{i:0;s:1:""3"";i:1;s:1:""z"";i:2;s:1:""2"";i:3;s:8:""jérôme"";i:4;s:10:""2014-02-16"";}|3"' . "\n" .
                    '"a:5:{i:0;s:1:""4"";i:1;s:1:""t"";i:2;s:1:""5"";i:3;s:5:""julie"";i:4;s:10:""2014-02-22"";}|4"' . "\n" .
                    '"a:5:{i:0;s:1:""5"";i:1;s:1:""t"";i:2;s:1:""5"";i:3;s:4:""abel"";i:4;s:10:""2014-02-01"";}|5"' . "\n" .
                    '"a:5:{i:0;s:1:""6"";i:1;s:1:""c"";i:2;s:1:""5"";i:3;s:5:""julie"";i:4;s:10:""2014-02-11"";}|6"' . "\n"
                )
        ;

        $conf = Loader::load([
            'driver' => [
                'name' => 'csv',
            ],
            'source' => [
                'name' => 'csv',
            ],
            'columns' => [
                '0' => [],
                '1' => [],
                '2' => [],
            ],
        ]);
        $this
            ->if($trieur = $this->newTestedInstance($conf, $this->csvPath()))
            ->string($trieur->getResponse())
                ->isEqualTo(
                    '1,a,3' . "\n" .
                    '2,z,2' . "\n" .
                    '3,z,2' . "\n" .
                    '4,t,5' . "\n" .
                    '5,t,5' . "\n" .
                    '6,c,5' . "\n"
                )
        ;
    }
}
