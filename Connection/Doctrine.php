<?php

namespace Solire\Trieur\Connection;

use \Solire\Trieur\Connection as ConnectionInterface;
use \Solire\Trieur\Driver;
use Solire\Conf\Conf;
use \Doctrine\DBAL\Connection as DoctrineConnection;

/**
 * Doctrine connection wrapper
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Doctrine implements ConnectionInterface
{
    /**
     * The database doctrine connection
     *
     * @var DoctrineConnection
     */
    protected $connection;

    /**
     * The driver
     *
     * @var Driver
     */
    protected $driver;

    /**
     * The configuration
     *
     * @var Conf
     */
    protected $conf;

    /**
     * The main doctrine query builder (cloned for each query)
     *
     * @var \Doctrine\DBAL\Query\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Constructor
     *
     * @param DoctrineConnection $connection The connection
     * @param Driver             $driver     The driver
     * @param Conf               $conf       The configuration
     */
    public function __construct(
        $connection,
        Driver $driver,
        Conf $conf
    ) {
        $this->connection = $connection;
        $this->driver     = $driver;
        $this->conf       = $conf;

        $this->buildQuery();
    }

    /**
     * Builds the raw query
     *
     * @return void
     */
    protected function buildQuery()
    {
        $this->queryBuilder = $this->connection->createQueryBuilder();

        $this->queryBuilder->select((array) $this->conf->select);

        /*
         * Main table
         */
        $this->queryBuilder->from(
            $this->conf->from->name,
            $this->conf->from->alias
        );

        /*
         * Inner join, right join, left join
         */
        $joinTypes = [
            'innerJoin',
            'leftJoin',
            'rightJoin',
        ];

        foreach ($joinTypes as $joinType) {
            if (isset($this->conf->$joinType)) {
                $joins = $this->conf->$joinType;
                $this->buildJoins($joinType, $joins);
            }
        }

        /*
         * Condition
         */
        if (isset($this->conf->where)) {
            $wheres = $this->conf->where;
            foreach ($wheres as $where) {
                $this->queryBuilder->innandWhere((array) $where);
            }
        }
    }

    /**
     * Add the joins to the main query builder
     *
     * @param string $joinType The join types 'innerJoin', 'leftJoin', 'rightJoin'
     * @param array  $joins    An array of joins (defined by an object with at
     * least 'name', 'alias' and 'on' keys)
     *
     * @return void
     */
    protected function buildJoins($joinType, $joins)
    {
        foreach ($joins as $join) {
            $this->queryBuilder->$joinType(
                $this->conf->from->alias,
                $join->name,
                $join->alias,
                $join->on
            );
        }
    }

    /**
     * Returns the main query builder
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getQuery()
    {
        return $this->queryBuilder;
    }

    /**
     * Build the filtered query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function buildFilteredQuery()
    {
        $queryBuilder = clone $this->queryBuilder;

        $term = $this->driver->getFilterTerm();
        if (!empty($term)) {
            $columns = $this->driver->getSearchableColumns();
            list($where, $order) = $this->search($term, $columns);
            $queryBuilder->andWhere($where);
            $queryBuilder->addOrderBy($order, 'DESC');
        }

        return $queryBuilder;
    }

    /**
     * Build the data query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getDataQuery()
    {
        $queryBuilder = $this->buildFilteredQuery();

        $queryBuilder->setFirstResult($this->driver->offset());
        $queryBuilder->setMaxResults($this->driver->length());

        $orders = $this->driver->order();
        foreach ($orders as $order) {
            list($col, $dir) = $order;
            $queryBuilder->addOrderBy($col, $dir);
        }

        return $queryBuilder;
    }

    /**
     * Build the count of raw query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getCountQuery()
    {
        $queryBuilder = clone $this->queryBuilder;

        $queryBuilder->select('COUNT(DISTINCT ' . $this->conf->group . ')');

        return $queryBuilder;
    }

    /**
     * Build the count of filtered query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getFilteredCountQuery()
    {
        $queryBuilder = $this->buildFilteredQuery();

        $queryBuilder->select('COUNT(DISTINCT ' . $this->conf->group . ')');

        return $queryBuilder;
    }

    /**
     * Return the total of available lines
     *
     * @return int Total number
     */
    public function getCount()
    {
        return $this->getCountQuery()
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Return the total of available lines filtered by the current search
     *
     * @return int Total number
     */
    public function getFilteredCount()
    {
        return $this->getFilteredCountQuery()
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Returns the data if there's a current search, filtered by the search
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->getDataQuery()
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Return the sort elements (WHERE et ORDER BY) for a search request
     *
     * @param string   $term    Term of search
     * @param string[] $columns Searchable table columns / sql expressions
     *
     * @return array
     */
    public function search($term, $columns)
    {
        /*
         * Variable qui contient la chaine de recherche
         */
        $stringSearch = trim($term);

        /*
         * On divise en mots (séparé par des espace)
         */
        $words = preg_split('`\s+`', $stringSearch);

        if (count($words) > 1) {
            array_unshift($words, $stringSearch);
        }

        $filterWords = [];
        $orderBy     = [];
        foreach ($words as $word) {
            foreach ($columns as $key => $value) {
                if (is_numeric($value)) {
                    $pond    = $value;
                    $colName = $key;
                } else {
                    $pond    = 1;
                    $colName = $value;
                }

                $filterWord     = $colName . ' LIKE '
                                . $this->connection->quote('%' . $word . '%');
                $filterWords[]  = $filterWord;
                $orderBy[]      = 'IF(' . $filterWord . ', ' . mb_strlen($word) * $pond . ', 0)';
            }
        }

        return [
            ' (' . implode(' OR ', $filterWords) . ')',
            ' ' . implode(' + ', $orderBy),
        ];
    }
}
