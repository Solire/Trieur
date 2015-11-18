<?php

namespace Solire\Trieur\test\units\Source\Doctrine;

use atoum as Atoum;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Solire\Trieur\Source\Doctrine\Exact as TestClass;

/**
 * Description of Contain
 *
 * @author thansen
 */
class Exact extends Atoum
{
    /**
     * Connection bdd
     *
     * @var Connection
     */
    private $connection = null;

    /**
     * Connect to the bdd
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
        $this->connection = new \mock\Doctrine\DBAL\Connection;
        $this->connection->getMockController()->connect = function() {};
        $this->connection->getMockController()->quote = function($input) {
            return '"' . addslashes($input) . '"';
        };

        $this->mockGenerator->unshuntParentClassCalls();

        return $this->connection;
    }

    /**
     *
     *
     * @return array
     */
    private function getColumns()
    {
        $columns = ['t.a'];
        return $columns;
    }

    /**
     *
     *
     * @return TestClass
     */
    public function testConstruct01()
    {
        $columns = $this->getColumns();
        $terms = 'abc';

        $this
            ->object($contain = new TestClass($columns, $terms))
        ;
        return $contain;
    }

    /**
     *
     *
     * @return TestClass
     */
    public function testConstruct02()
    {
        $columns = $this->getColumns();
        $terms = ['abc', 'a a a '];

        $this
            ->object($contain = new TestClass($columns, $terms))
        ;
        return $contain;
    }

    /**
     *
     *
     * @return TestClass
     */
    public function testSetQueryBuilder01()
    {
        $contain = $this->testConstruct01();

        $queryBuilder = new QueryBuilder($this->getConnection());
        $queryBuilder->select('*')->from('table', 't');

        $contain->setQueryBuilder($queryBuilder);

        return $contain;
    }

    /**
     *
     *
     * @return TestClass
     */
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
                    . 't.a = "abc" '
                    . 'OR t.a = "a a a "'
                )
        ;
    }
}
