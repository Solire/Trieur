<?php

namespace Solire\Trieur\Source;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Solire\Conf\Conf;
use Solire\Trieur\Columns;
use Solire\Trieur\Source;
use Solire\Trieur\Source\DoctrineOrm\Filter;
use Solire\Trieur\SourceFilter;

/**
 * Description of DoctrineOrm
 *
 * @author thansen
 */
class DoctrineOrm extends Source
{
    /**
     * The entity manager
     *
     * @var EntityManager
     */
    protected $connection;

    /**
     * The main doctrine query builder (cloned for each query)
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * The main doctrine query builder (cloned for each query)
     *
     * @var QueryBuilder
     */
    protected $currentQueryBuilder;

    /**
     * Constructor
     *
     * @param Conf          $conf       The configuration
     * @param Columns       $columns    The columns configuration
     * @param EntityManager $connection The entity manager
     */
    public function __construct(
        Conf $conf,
        Columns $columns,
        EntityManager $connection
    ) {
        parent::__construct($conf, $columns, $connection);

        $this->buildQuery();
    }

    /**
     * Returns the main query builder
     *
     * @return QueryBuilder
     */
    public function getQuery()
    {
        return $this->queryBuilder;
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
        foreach ($this->conf->from as $from) {
            $this->queryBuilder->from(
                $from->name,
                $from->alias
            );
        }

        /*
         * Inner join, right join, left join
         */
        $joinTypes = [
            'join',
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

        if (isset($this->conf->parameters)) {
            foreach ($this->conf->parameters as $key => $value) {
                $this->queryBuilder->setParameter($key, $value);
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
                $join->name,
                $join->alias,
                $join->type,
                $join->on
            );
        }
    }

    public function getCount()
    {
        return $this->getCountQuery()->getQuery()->getResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    public function getData()
    {
        return $this->getDataQuery()->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    public function getFilteredCount()
    {
        return $this->getFilteredCountQuery()->getQuery()->getResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Build the count of raw query
     *
     * @return QueryBuilder
     */
    public function getCountQuery()
    {
        $this->currentQueryBuilder = clone $this->queryBuilder;

        $this->currentQueryBuilder->select('COUNT(DISTINCT ' . $this->getDistinct() . ')');
        $this->currentQueryBuilder->resetDQLPart('orderBy');

        return $this->currentQueryBuilder;
    }

    /**
     * Build the data query
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
     * Build the count of filtered query
     *
     * @return QueryBuilder
     */
    public function getFilteredCountQuery()
    {
        $this->buildFilteredQuery();

        $this->currentQueryBuilder->select('COUNT(DISTINCT ' . $this->getDistinct() . ')');
        $this->currentQueryBuilder->resetDQLPart('orderBy');

        return $this->currentQueryBuilder;
    }

    /**
     * Returns the sql expression to determinate the distincts numbers of lines
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
            $this->queryBuilder->getDQLPart('select')
        );
    }

    /**
     * Process the filter
     *
     * @param Filter $filter The filter class
     *
     * @return void
     */
    protected function processFilter(SourceFilter $filter)
    {
        $filter->setQueryBuilder($this->currentQueryBuilder);
        $filter->filter();
    }

    /**
     * Build the filtered query
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
}
