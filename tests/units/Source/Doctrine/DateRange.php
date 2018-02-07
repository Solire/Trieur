<?php

namespace Solire\Trieur\test\units\Source\Doctrine;

use atoum;
use Doctrine\DBAL\Connection;

/**
 * Description of Contain.
 *
 * @author thansen
 */
class DateRange extends atoum
{
    /**
     * Connection bdd.
     *
     * @var Connection
     */
    private $connection = null;

    /**
     * Connect to the bdd.
     *
     * @return Connection
     */
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

    private function createQueryBuilder()
    {
        $queryBuilder = new \mock\Doctrine\DBAL\Query\QueryBuilder($this->getConnection());
        $queryBuilder->getMockController()->expr = function () {
            return new \mock\Doctrine\DBAL\Query\Expression\ExpressionBuilder($this->getConnection());
        };

        return $queryBuilder;
    }

    /**
     * @return array
     */
    private function getColumns()
    {
        $columns = ['t.a'];

        return $columns;
    }

    public function testConstruct01()
    {
        $columns = $this->getColumns();
        $terms = [
            '1970-12-01',
            '1972-01-12',
        ];

        $this
            ->object($contain = $this->newTestedInstance($columns, $terms))
        ;

        return $contain;
    }

    public function testSetQueryBuilder01()
    {
        $contain = $this->testConstruct01();

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->select('*')->from('table', 't');

        $contain->setQueryBuilder($queryBuilder);

        return $contain;
    }

    public function testFilter01()
    {
        $contain = $this->testSetQueryBuilder01();

        $contain->filter();

        $this
            ->string($contain->getQueryBuilder()->getSQL())
                ->isEqualTo(
                    'SELECT * FROM table t WHERE '
                    . '(t.a >= "1970-12-01") '
                    . 'AND (t.a <= "1972-01-12")'
                )
        ;
    }

    public function testConstruct02()
    {
        $columns = $this->getColumns();
        $terms = [
            '1970-12-01',
            null,
        ];

        $this
            ->object($contain = $this->newTestedInstance($columns, $terms))
        ;

        return $contain;
    }

    public function testSetQueryBuilder02()
    {
        $contain = $this->testConstruct02();

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->select('*')->from('table', 't');

        $contain->setQueryBuilder($queryBuilder);

        return $contain;
    }

    public function testFilter02()
    {
        $contain = $this->testSetQueryBuilder02();

        $contain->filter();

        $this
            ->string($contain->getQueryBuilder()->getSQL())
                ->isEqualTo(
                    'SELECT * FROM table t WHERE '
                    . 't.a >= "1970-12-01"'
                )
        ;
    }

    public function testConstruct03()
    {
        $columns = $this->getColumns();
        $terms = [
            null,
            '1970-12-01',
        ];

        $this
            ->object($contain = $this->newTestedInstance($columns, $terms))
        ;

        return $contain;
    }

    public function testSetQueryBuilder03()
    {
        $contain = $this->testConstruct03();

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->select('*')->from('table', 't');

        $contain->setQueryBuilder($queryBuilder);

        return $contain;
    }

    public function testFilter03()
    {
        $contain = $this->testSetQueryBuilder03();

        $contain->filter();

        $this
            ->string($contain->getQueryBuilder()->getSQL())
                ->isEqualTo(
                    'SELECT * FROM table t WHERE '
                    . 't.a <= "1970-12-01"'
                )
        ;
    }
}
