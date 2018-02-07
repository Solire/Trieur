<?php

namespace Solire\Trieur\Source;

use Solire\Trieur\Source;
use Solire\Trieur\Columns;
use Solire\Conf\Conf;
use Solire\Trieur\SourceFilter;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Doctrine connection wrapper.
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Doctrine extends Source
{
    /**
     * The connection.
     *
     * @var DoctrineConnection
     */
    protected $connection;

    /**
     * The main doctrine query builder (cloned for each query).
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * The main doctrine query builder (cloned for each query).
     *
     * @var QueryBuilder
     */
    protected $currentQueryBuilder;

    /**
     * Constructor.
     *
     * @param Conf               $conf       The configuration
     * @param Columns            $columns    The columns configuration
     * @param DoctrineConnection $connection The connection
     */
    public function __construct(
        Conf $conf,
        Columns $columns,
        DoctrineConnection $connection
    ) {
        parent::__construct($conf, $columns, $connection);

        $this->buildQuery();
    }

    /**
     * Returns the sql expression to determinate the distincts numbers of lines.
     *
     * @return string
     */
    protected function getDistinct()
    {
        if (isset($this->conf->group)) {
            return $this->conf->group;
        }

        return implode(
            ', ',
            $this->queryBuilder->getQueryPart('select')
        );
    }

    /**
     * Builds the raw query.
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
            foreach ($this->conf->where as $where) {
                $this->queryBuilder->andWhere($where);
            }
        }
    }

    /**
     * Add the joins to the main query builder.
     *
     * @param string $joinType The join types 'innerJoin', 'leftJoin', 'rightJoin'
     * @param array  $joins    An array of joins (defined by an object with at
     *                         least 'name', 'alias' and 'on' keys)
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
     * Returns the main query builder.
     *
     * @return QueryBuilder
     */
    public function getQuery()
    {
        return $this->queryBuilder;
    }

    /**
     * Build the filtered query.
     *
     * @return void
     */
    protected function buildFilteredQuery()
    {
        $this->currentQueryBuilder = clone $this->queryBuilder;

        if ($this->filters !== null) {
            $this->filter();
        }
    }

    /**
     * Process the filter.
     *
     * @param Doctrine\Filter $filter The filter class
     *
     * @return void
     */
    protected function processFilter(SourceFilter $filter)
    {
        $filter->setQueryBuilder($this->currentQueryBuilder);
        $filter->filter();
    }

    /**
     * Build the data query.
     *
     * @return QueryBuilder
     */
    public function getDataQuery()
    {
        $this->buildFilteredQuery();

        if ($this->offset !== null) {
            $this->currentQueryBuilder->setFirstResult($this->offset);
        }

        if ($this->length !== null) {
            $this->currentQueryBuilder->setMaxResults($this->length);
        }

        if ($this->orders !== null) {
            foreach ($this->orders as $order) {
                list($column, $dir) = $order;

                $this->currentQueryBuilder->addOrderBy(
                    $column->sourceSort,
                    $dir
                );
            }
        }

        if (isset($this->conf->group)) {
            $this->currentQueryBuilder->groupBy($this->conf->group);
        }

        return $this->currentQueryBuilder;
    }

    /**
     * Build the count of raw query.
     *
     * @return QueryBuilder
     */
    public function getCountQuery()
    {
        $this->currentQueryBuilder = clone $this->queryBuilder;

        $this->currentQueryBuilder->select('COUNT(DISTINCT ' . $this->getDistinct() . ')');
        $this->currentQueryBuilder->resetQueryPart('orderBy');

        return $this->currentQueryBuilder;
    }

    /**
     * Build the count of filtered query.
     *
     * @return QueryBuilder
     */
    public function getFilteredCountQuery()
    {
        $this->buildFilteredQuery();

        $this->currentQueryBuilder->select('COUNT(DISTINCT ' . $this->getDistinct() . ')');
        $this->currentQueryBuilder->resetQueryPart('orderBy');

        return $this->currentQueryBuilder;
    }

    /**
     * Return the total of available lines.
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
     * Return the total of available lines filtered by the current filters.
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
     * Returns the data filtered by the current filters.
     *
     * @return mixed
     */
    public function getData()
    {
        $data = $this->getDataQuery()
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }
}
