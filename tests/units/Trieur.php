<?php
namespace Solire\Trieur\test\units;

use \atoum as Atoum;
use Solire\Trieur\Trieur as TestClass;

use Solire\Conf\Conf;

class Trieur extends Atoum
{
    /**
     *
     *
     * @var \Doctrine\DBAL\Connection
     */
    public $connection = null;

    /**
     *
     *
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
     *
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->mockGenerator->shuntParentClassCalls();

        $this->mockGenerator->orphanize('__construct');
        $pdo = new \mock\PDO;

        $this->mockGenerator->orphanize('__construct');
        $this->connection = new \mock\Doctrine\DBAL\Connection;

        $this->mockGenerator->unshuntParentClassCalls();

        return $this->connection;
    }

    public function testConstruct()
    {
        $conf = new Conf;
        $this
            ->exception(function()use($conf){
                new TestClass($conf);
            })
            ->hasMessage('No class for driver class founed or given')
        ;

        $conf = arrayToConf([
            'driver' => [
                'name' => 'dataTables',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ]
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
            ->exception(function()use($conf){
                new TestClass($conf, true);
            })
            ->hasMessage('No wrapper class for source class founed')
        ;

        $conf = arrayToConf([
            'driver' => [
                'name' => 'dataTables',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ]
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
            ->object(new TestClass($conf, $doctrineConnection))
        ;

        $conf = arrayToConf([
            'driver' => [
                'class' => '\Solire\Trieur\Driver\Csv',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ]
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
            ->object(new TestClass($conf, $doctrineConnection))
        ;

        $conf = arrayToConf([
            'driver' => [
                'class' => '\stdClass',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ]
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
            ->exception(function()use($conf){
                new TestClass($conf);
            })
            ->hasMessage('class "\stdClass" does not extend abstract class "\Solire\Trieur\Driver"')
        ;
        $conf = arrayToConf([
            'driver' => [
                'class' => '\stddddClass',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ]
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
            ->exception(function()use($conf){
                new TestClass($conf);
            })
            ->hasMessage('class "\stddddClass" does not exist')
        ;

        $conf = arrayToConf([
            'driver' => [
                'class' => '\Solire\Trieur\Driver\Csv',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ]
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
            ->exception(function()use($conf){
                new TestClass($conf, true);
            })
            ->hasMessage('class "\PDO" does not extend abstract class "\Solire\Trieur\Source"')
        ;

        $conf = arrayToConf([
            'driver' => [
                'class' => '\Solire\Trieur\Driver\Csv',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ]
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
            ->exception(function()use($conf){
                new TestClass($conf, true);
            })
            ->hasMessage('class "\PDOHOHOH" does not exist')
        ;
    }

    public function testGet()
    {
        $conf = arrayToConf([
            'driver' => [
                'name' => 'dataTables',
                'conf' => [
                    'itemName' => 'objet',
                    'itemsName' => 'objets',
                    'itemsGenre' => '',
                ]
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
            ->if($trieur = new TestClass($conf, $doctrineConnection))
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
        $conf = arrayToConf([
            'driver' => [
                'name' => 'csv',
            ],
            'source' => [
                'name' => 'csv',
            ],
            'columns' => [
                '0' => [
                    'view' => 'notexisting.php',
                ],
            ],
        ]);
        $this
            ->if($trieur = new TestClass($conf, $this->csvPath()))
            ->exception(function()use($trieur){
                $trieur->getResponse();
            })
                ->hasMessage('The view file "notexisting.php" does not exist or is not readable')
        ;

        $conf = arrayToConf([
            'driver' => [
                'name' => 'csv',
            ],
            'source' => [
                'name' => 'csv',
            ],
            'columns' => [
                '4' => [
                    'callback' => 'Solire\Trieur\Example\Format::sqlTo',
                ],
            ],
        ]);
        $this
            ->if($trieur = new TestClass($conf, $this->csvPath()))
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

        $conf = arrayToConf([
            'driver' => [
                'name' => 'csv',
            ],
            'source' => [
                'name' => 'csv',
            ],
            'columns' => [
                '0' => [
                    'callback' => [
                        'name' => '\Solire\Trieur\Example\Format::serialize',
                        'row' => 0,
                        'cell' => 1,
                    ],
                ],
            ],
        ]);
        $this
            ->if($trieur = new TestClass($conf, $this->csvPath()))
            ->string($trieur->getResponse())
                ->isEqualTo(
                    '"a:1:{i:0;s:1:""1"";}|1"' . "\n" .
                    '"a:1:{i:0;s:1:""2"";}|2"' . "\n" .
                    '"a:1:{i:0;s:1:""3"";}|3"' . "\n" .
                    '"a:1:{i:0;s:1:""4"";}|4"' . "\n" .
                    '"a:1:{i:0;s:1:""5"";}|5"' . "\n" .
                    '"a:1:{i:0;s:1:""6"";}|6"' . "\n"
                )
        ;

        $conf = arrayToConf([
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
                '3' => [
                    'hide' => true,
                ],
                '4' => [
                    'callback' => [
                        'name' => 'Solire\Trieur\Example\Format::sqlTo',
                        'cell' => 0,
                        'arguments' => [
                            'd/m/Y',
                        ],
                    ],
                ],
                '5' => [
                    'source' => 0,
                    'view' => 'view.php',
                ],
            ],
        ]);
        $this
            ->if($trieur = new TestClass($conf, $this->csvPath()))
            ->string($trieur->getResponse())
                ->isEqualTo(
                    '1,a,3,06/02/2014,<b>1|a|3|thomas|2014-02-06|1</b>' . "\n" .
                    '2,z,2,08/02/2014,<b>2|z|2|thomas|2014-02-08|2</b>' . "\n" .
                    '3,z,2,16/02/2014,<b>3|z|2|jérôme|2014-02-16|3</b>' . "\n" .
                    '4,t,5,22/02/2014,<b>4|t|5|julie|2014-02-22|4</b>' . "\n" .
                    '5,t,5,01/02/2014,<b>5|t|5|abel|2014-02-01|5</b>' . "\n" .
                    '6,c,5,11/02/2014,<b>6|c|5|julie|2014-02-11|6</b>' . "\n"
                )
        ;

        $this
            ->if($trieur = new TestClass($conf, $this->csvPath()))
            ->string($trieur->getResponse())
                ->isEqualTo(
                    '1,a,3,06/02/2014,<b>1|a|3|thomas|2014-02-06|1</b>' . "\n" .
                    '2,z,2,08/02/2014,<b>2|z|2|thomas|2014-02-08|2</b>' . "\n" .
                    '3,z,2,16/02/2014,<b>3|z|2|jérôme|2014-02-16|3</b>' . "\n" .
                    '4,t,5,22/02/2014,<b>4|t|5|julie|2014-02-22|4</b>' . "\n" .
                    '5,t,5,01/02/2014,<b>5|t|5|abel|2014-02-01|5</b>' . "\n" .
                    '6,c,5,11/02/2014,<b>6|c|5|julie|2014-02-11|6</b>' . "\n"
                )
        ;

        $conf = arrayToConf([
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
            ->if($trieur = new TestClass($conf, $this->csvPath()))
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
