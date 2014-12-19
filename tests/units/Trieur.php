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
            ['1', 'a', '3', 'thomas'],
            ['2', 'z', '2', 'thomas'],
            ['3', 'z', '2', 'jérôme'],
            ['4', 't', '5', 'julie'],
            ['5', 't', '5', 'abel'],
            ['6', 'c', '5', 'julie'],
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
                new TestClass($conf);
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
                new TestClass($conf);
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
                new TestClass($conf);
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
