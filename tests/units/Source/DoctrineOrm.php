<?php
namespace Solire\Trieur\test\units\Source;

use atoum as Atoum;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Setup;
use Solire\Conf\Conf;
use Solire\Trieur\Columns;
use Solire\Trieur\Source\DoctrineOrm as TestClass;
use Solire\Trieur\tests\data\Entity\Profil;

class MockDatabasePF extends \Doctrine\DBAL\Platforms\MySqlPlatform
{
}

class DoctrineOrm extends Atoum
{
    /**
     * Manager d'entité
     *
     * @var EntityManager
     */
    public $connection = null;

    /**
     *
     * @return EntityManager
     */
    private function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->mockGenerator->shuntParentClassCalls();

        $this->mockGenerator->orphanize('__construct');
        $pdo = new \mock\PDO;

        $this->mockGenerator->orphanize('__construct');
        $db = new \mock\Doctrine\DBAL\Connection;
        $db->getMockController()->connect = function() {};
        $db->getMockController()->getEventManager = function() {
            return new EventManager();;
        };
        $db->getMockController()->getDatabasePlatform = function() {
            return new MockDatabasePF;
        };
        $this->mockGenerator->unshuntParentClassCalls();

        $config = Setup::createYAMLMetadataConfiguration([
            TEST_DATA_DIR . '/doctrine-orm'
        ], true);

        $this->connection = EntityManager::create($db, $config);

        return $this->connection;
    }

    public function testConstruct01()
    {
        $connection = $this->getConnection();

        $conf = new Conf;
        $conf->select = [
            'c.nom'
        ];
        $conf->from = new Conf;
        $conf->from->name = Profil::class;
        $conf->from->alias = 'c';

        $columns = new Columns(new Conf);

        $this
            ->if($c = new TestClass($conf, $columns, $connection))
                ->object($c)
                ->object($qB = $c->getQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.nom FROM ' . Profil::class . ' c')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
        ;

//                ->object($qB = $c->getDataQuery())
//                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
//                    ->string($qB->getSQL())
//                    ->isEqualTo('SELECT a, v FROM tt t')
//
//                ->object($qB = $c->getCountQuery())
//                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
//                    ->string($qB->getSQL())
//                    ->isEqualTo('SELECT COUNT(DISTINCT a, v) FROM tt t')
//
//                ->object($qB = $c->getFilteredCountQuery())
//                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
//                    ->string($qB->getSQL())
//                    ->isEqualTo('SELECT COUNT(DISTINCT a, v) FROM tt t')
        ;
    }

//    public function testConstruct02()
//    {
//        $connection = $this->getConnection();
//
//        $conf = arrayToConf([
//            'select' => [
//                'a',
//                'v',
//            ],
//            'from' => [
//                'name' => 'tt',
//                'alias' => 't',
//            ],
//            'where' => [
//                'a = v',
//            ],
//            'innerJoin' => [
//                [
//                    'name' => 'uu',
//                    'alias' => 'u',
//                    'on' => 'u.c = t.v',
//                ]
//            ]
//        ]);
//
//        $columns = new Columns(new Conf);
//
//        $this
//            ->if($c = new TestClass($conf, $columns, $connection))
//                ->object($c)
//                ->object($qB = $c->getQuery())
//                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
//
//                ->string($qB->getSQL())
//                    ->isEqualTo('SELECT a, v FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')
//
//                ->object($qB = $c->getDataQuery())
//                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
//                    ->string($qB->getSQL())
//                    ->isEqualTo('SELECT a, v FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')
//
//                ->object($qB = $c->getCountQuery())
//                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
//                    ->string($qB->getSQL())
//                    ->isEqualTo('SELECT COUNT(DISTINCT a, v) FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')
//
//                ->object($qB = $c->getFilteredCountQuery())
//                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
//                    ->string($qB->getSQL())
//                    ->isEqualTo('SELECT COUNT(DISTINCT a, v) FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')
//        ;
//    }
//    public function testConstruct03()
//    {
//        $connection = $this->getConnection();
//
//        $conf = arrayToConf([
//            'select' => [
//                'a',
//                'v',
//            ],
//            'from' => [
//                'name' => 'tt',
//                'alias' => 't',
//            ],
//            'where' => [
//                'a = v',
//            ],
//            'innerJoin' => [
//                [
//                    'name' => 'uu',
//                    'alias' => 'u',
//                    'on' => 'u.c = t.v',
//                ]
//            ],
//            'group' => 't.a',
//        ]);
//
//        $columns = new Columns(arrayToConf([
//            'a' => [
//                'source' => 't.a'
//            ]
//        ]));
//
//        $this
//            ->if($c = new TestClass($conf, $columns, $connection))
//            ->and($c->addOrder('a', 'ASC'))
//            ->and($c->addFilter([
//                ['t.a'],
//                'trieur php',
//                'Contain'
//            ]))
//            ->and($c->setOffset(10))
//            ->and($c->setLength(5))
//
//            ->and($qB = $c->getQuery())
//            ->string($qB->getSQL())
//                ->isEqualTo('SELECT a, v FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')
//
//            ->and($qB = $c->getDataQuery())
//            ->string($qB->getSQL())
//                ->isEqualTo(
//                    'SELECT a, v '
//                    . 'FROM tt t '
//                    . 'INNER JOIN uu u ON u.c = t.v '
//                    . 'WHERE (a = v) '
//                    . 'AND (t.a LIKE "%trieur php%" OR t.a LIKE "%trieur%" OR t.a LIKE "%php%") '
//                    . 'GROUP BY t.a '
//                    . 'ORDER BY IF(t.a LIKE "%trieur php%", 10, 0) + IF(t.a LIKE "%trieur%", 6, 0) + IF(t.a LIKE "%php%", 3, 0) DESC, '
//                    . 't.a '
//                    . 'ASC '
//                    . 'LIMIT 5 '
//                    . 'OFFSET 10'
//                )
//
//            ->and($qB = $c->getCountQuery())
//            ->string($qB->getSQL())
//                ->isEqualTo(
//                    'SELECT COUNT(DISTINCT t.a) '
//                    . 'FROM tt t '
//                    . 'INNER JOIN uu u ON u.c = t.v '
//                    . 'WHERE a = v'
//                )
//
//            ->and($qB = $c->getFilteredCountQuery())
//            ->string($qB->getSQL())
//                ->isEqualTo(
//                    'SELECT COUNT(DISTINCT t.a) '
//                    . 'FROM tt t '
//                    . 'INNER JOIN uu u '
//                    . 'ON u.c = t.v '
//                    . 'WHERE (a = v) '
//                    . 'AND (t.a LIKE "%trieur php%" OR t.a LIKE "%trieur%" OR t.a LIKE "%php%")'
//                )
//        ;
//
//    }
}
