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

    public function getCount()
    {
        ;
    }

    public function getData()
    {
        ;
    }

    public function getFilteredCount()
    {
        ;
    }

    protected function processFilter(SourceFilter $filter)
    {
        ;
    }
}
