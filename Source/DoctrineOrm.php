<?php

namespace Solire\Trieur\Source;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Solire\Conf\Conf;
use Solire\Trieur\Source;
use Solire\Trieur\SourceFilter;
use Solire\Trieur\Columns;

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
         * Condition
         */
        if (isset($this->conf->where)) {
            foreach ($this->conf->where as $where) {
                $this->queryBuilder->andWhere($where);
            }
        }
    }



    public function getCount()
    {
        return $this->getCountQuery()->getQuery()->getOneOrNullResult();
    }

    public function getData()
    {
        return $this->getDataQuery()->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }

    public function getFilteredCount()
    {
        return $this->getFilteredCountQuery()->getQuery()->getOneOrNullResult();
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
            $this->queryBuilder->getQueryPart('select')
        );
    }

    /**
     * Process the filter
     *
     * @param DoctrineOrm\Filter $filter The filter class
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
