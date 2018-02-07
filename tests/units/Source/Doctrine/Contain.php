<?php

namespace Solire\Trieur\test\units\Source\Doctrine;

use atoum;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Description of Contain.
 *
 * @author thansen
 */
class Contain extends atoum
{
    private $connection = null;

    private function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->mockGenerator->shuntParentClassCalls();

        $this->mockGenerator->orphanize('__construct');
        $this->connection = new \mock\Doctrine\DBAL\Connection();
        $this->connection->getMockController()->connect = function () {
        };
        $this->connection->getMockController()->quote = function ($input) {
            return '"' . addslashes($input) . '"';
        };

        $this->mockGenerator->unshuntParentClassCalls();

        return $this->connection;
    }

    private function getColumns()
    {
        $columns = ['t.a'];

        return $columns;
    }

    public function testConstruct01()
    {
        $columns = $this->getColumns();
        $terms = 'abc';

        $this
            ->object($contain = $this->newTestedInstance($columns, $terms))
        ;

        return $contain;
    }

    public function testConstruct02()
    {
        $columns = $this->getColumns();
        $terms = ['abc', 'a a a '];

        $this
            ->object($contain = $this->newTestedInstance($columns, $terms))
        ;

        return $contain;
    }

    public function testSetQueryBuilder01()
    {
        $contain = $this->testConstruct01();

        $queryBuilder = new QueryBuilder($this->getConnection());
        $queryBuilder->select('*')->from('table', 't');

        $contain->setQueryBuilder($queryBuilder);

        return $contain;
    }

    public function testSetQueryBuilder02()
    {
        $contain = $this->testConstruct02();

        $queryBuilder = new QueryBuilder($this->getConnection());
        $queryBuilder->select('*')->from('table', 't');

        $contain->setQueryBuilder($queryBuilder);

        return $contain;
    }

    public function testFilter01()
    {
        $contain = $this->testSetQueryBuilder01();

        $contain->filter();
    }

    public function testFilter02()
    {
        $contain = $this->testSetQueryBuilder02();

        $contain->filter();

        $this
            ->string($contain->getQueryBuilder()->getSQL())
                ->isEqualTo(
                    'SELECT * FROM table t WHERE '
                    . 't.a LIKE "%abc a a a %" '
                    . 'OR t.a LIKE "%abc%" '
                    . 'OR t.a LIKE "%a%" '
                    . 'ORDER BY IF(t.a LIKE "%abc a a a %", 10, 0) + '
                    . 'IF(t.a LIKE "%abc%", 3, 0) + '
                    . 'IF(t.a LIKE "%a%", 1, 0) DESC'
                )
        ;
    }
}
