<?php
namespace Solire\Trieur\test\units\Source;

use \atoum as Atoum;
use Solire\Trieur\Source\Doctrine as TestClass;

use Solire\Trieur\Columns;
use Solire\Conf\Conf;

class MockDatabasePF
{
    public function modifyLimitQuery($query, $limit, $offset)
    {
        if ($limit !== null) {
            $query .= ' LIMIT ' . $limit;
        }

        if ($offset !== null) {
            $query .= ' OFFSET ' . $offset;
        }

        return $query;
    }
}

class Doctrine extends Atoum
{
    public $connection = null;

    private function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->mockGenerator->shuntParentClassCalls();

        $this->mockGenerator->orphanize('__construct');

        $this->mockGenerator->orphanize('__construct');
        $this->connection = new \mock\Doctrine\DBAL\Connection;
        $this->connection->getMockController()->connect = function() {};
        $this->connection->getMockController()->quote = function($input, $type) {
            return '"' . addslashes($type) . '"';
        };

        $this->connection->getMockController()->getDatabasePlatform = function() {
            return new MockDPF;
        };

        $this->mockGenerator->unshuntParentClassCalls();

        return $this->connection;
    }

    public function testConstruct01()
    {
        $connection = $this->getConnection();

        $conf = new Conf;
        $conf->select = [
            'a',
            'v'
        ];
        $conf->from = new Conf;
        $conf->from->name = 'tt';
        $conf->from->alias = 't';

        $columns = new Columns(new Conf);

        $this
            ->if($c = new TestClass($conf, $columns, $connection))
                ->object($c)
                ->object($qB = $c->getQuery())
                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')

                ->string($qB->getSQL())
                    ->isEqualTo('SELECT a, v FROM tt t')

                ->object($qB = $c->getDataQuery())
                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
                    ->string($qB->getSQL())
                    ->isEqualTo('SELECT a, v FROM tt t')

                ->object($qB = $c->getCountQuery())
                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
                    ->string($qB->getSQL())
                    ->isEqualTo('SELECT COUNT(DISTINCT a, v) FROM tt t')

                ->object($qB = $c->getFilteredCountQuery())
                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
                    ->string($qB->getSQL())
                    ->isEqualTo('SELECT COUNT(DISTINCT a, v) FROM tt t')
        ;
    }

    public function testConstruct02()
    {
        $connection = $this->getConnection();

        $conf = arrayToConf([
            'select' => [
                'a',
                'v',
            ],
            'from' => [
                'name' => 'tt',
                'alias' => 't',
            ],
            'where' => [
                'a = v',
            ],
            'innerJoin' => [
                [
                    'name' => 'uu',
                    'alias' => 'u',
                    'on' => 'u.c = t.v',
                ]
            ]
        ]);

        $columns = new Columns(new Conf);

        $this
            ->if($c = new TestClass($conf, $columns, $connection))
                ->object($c)
                ->object($qB = $c->getQuery())
                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')

                ->string($qB->getSQL())
                    ->isEqualTo('SELECT a, v FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')

                ->object($qB = $c->getDataQuery())
                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
                    ->string($qB->getSQL())
                    ->isEqualTo('SELECT a, v FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')

                ->object($qB = $c->getCountQuery())
                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
                    ->string($qB->getSQL())
                    ->isEqualTo('SELECT COUNT(DISTINCT a, v) FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')

                ->object($qB = $c->getFilteredCountQuery())
                    ->isInstanceOf('\Doctrine\DBAL\Query\QueryBuilder')
                    ->string($qB->getSQL())
                    ->isEqualTo('SELECT COUNT(DISTINCT a, v) FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')
        ;
    }
    public function testConstruct03()
    {
        $connection = $this->getConnection();

        $conf = arrayToConf([
            'select' => [
                'a',
                'v',
            ],
            'from' => [
                'name' => 'tt',
                'alias' => 't',
            ],
            'where' => [
                'a = v',
            ],
            'innerJoin' => [
                [
                    'name' => 'uu',
                    'alias' => 'u',
                    'on' => 'u.c = t.v',
                ]
            ],
            'group' => 't.a',
        ]);

        $columns = new Columns(arrayToConf([
            'a' => [
                'source' => 't.a'
            ]
        ]));

        $this
            ->if($c = new TestClass($conf, $columns, $connection))
            ->and($c->addOrder('a', 'ASC'))
            ->and($c->addSearch([
                't.a',
                'trieur php',
                'Contain'
            ]))
            ->and($c->setOffset(10))
            ->and($c->setLength(5))

            ->and($qB = $c->getQuery())
            ->string($qB->getSQL())
                ->isEqualTo('SELECT a, v FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')

            ->and($qB = $c->getDataQuery())
            ->string($qB->getSQL())
                ->isEqualTo('SELECT a, v FROM tt t INNER JOIN uu u ON u.c = t.v WHERE (a = v) AND (t.a LIKE "" OR t.a LIKE "" OR t.a LIKE "") GROUP BY t.a ORDER BY IF(t.a LIKE "", 10, 0) + IF(t.a LIKE "", 6, 0) + IF(t.a LIKE "", 3, 0) DESC, t.a ASC LIMIT 5 OFFSET 10')

            ->and($qB = $c->getCountQuery())
            ->string($qB->getSQL())
                ->isEqualTo('SELECT COUNT(DISTINCT t.a) FROM tt t INNER JOIN uu u ON u.c = t.v WHERE a = v')

            ->and($qB = $c->getFilteredCountQuery())
            ->string($qB->getSQL())
                ->isEqualTo('SELECT COUNT(DISTINCT t.a) FROM tt t INNER JOIN uu u ON u.c = t.v WHERE (a = v) AND (t.a LIKE "" OR t.a LIKE "" OR t.a LIKE "") ORDER BY IF(t.a LIKE "", 10, 0) + IF(t.a LIKE "", 6, 0) + IF(t.a LIKE "", 3, 0) DESC')
        ;

    }
}
