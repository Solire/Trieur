<?php
namespace Solire\Trieur\test\units\Connection;

use \atoum as Atoum;
use Solire\Trieur\Connection\Doctrine as TestClass;

use Solire\Conf\Conf;

class Doctrine extends Atoum
{
    public $connection = null;

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

        $this
            ->if($c = new TestClass($connection, $conf))
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

        $conf = new Conf;
        $conf->select = [
            'a',
            'v'
        ];
        $conf->from = new Conf;
        $conf->from->name = 'tt';
        $conf->from->alias = 't';
        $conf->where = ['a = v'];
        $conf->innerJoin = new Conf;
        $conf->innerJoin[0] = new Conf;
        $conf->innerJoin[0]->name = 'uu';
        $conf->innerJoin[0]->alias = 'u';
        $conf->innerJoin[0]->on = 'u.c = t.v';

        $this
            ->if($c = new TestClass($connection, $conf))
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
}
