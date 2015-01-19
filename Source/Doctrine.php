<?php
namespace Solire\Trieur\Source;

use Solire\Trieur\Source;
use Solire\Trieur\Columns;
use Solire\Conf\Conf;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Doctrine connection wrapper
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Doctrine extends Source
{
    /**
     * The connection
     *
     * @var DoctrineConnection
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
     * @param DoctrineConnection $connection The connection
     * @param Conf               $conf       The configuration
     * @param Columns            $columns    The columns configuration
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
            $wheres = (array) $this->conf->where;
            foreach ($wheres as $where) {
                $this->queryBuilder->andWhere($where);
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
     * @return QueryBuilder
     */
    public function getQuery()
    {
        return $this->queryBuilder;
    }

    /**
     * Build the filtered query
     *
     * @return QueryBuilder
     */
    protected function buildFilteredQuery()
    {
        $queryBuilder = clone $this->queryBuilder;

        if ($this->searches !== null) {
            $this->buildSearch($queryBuilder);
        }

        return $queryBuilder;
    }

    /**
     * Build the data query
     *
     * @return QueryBuilder
     */
    public function getDataQuery()
    {
        $queryBuilder = $this->buildFilteredQuery();

        if ($this->offset !== null) {
            $queryBuilder->setFirstResult($this->offset);
        }

        if ($this->length !== null) {
            $queryBuilder->setMaxResults($this->length);
        }

        if ($this->orders !== null) {
            foreach ($this->orders as $order) {

                list($column, $dir) = $order;

                $queryBuilder->addOrderBy(
                    $this->columns->getColumnSourceSort($column),
                    $dir
                );
            }
        }

        if (isset($this->conf->group)) {
            $queryBuilder->groupBy($this->conf->group);
        }

        return $queryBuilder;
    }

    /**
     * Build the count of raw query
     *
     * @return QueryBuilder
     */
    public function getCountQuery()
    {
        $queryBuilder = clone $this->queryBuilder;

        $queryBuilder->select('COUNT(DISTINCT ' . $this->getDistinct() . ')');

        return $queryBuilder;
    }

    /**
     * Build the count of filtered query
     *
     * @return QueryBuilder
     */
    public function getFilteredCountQuery()
    {
        $queryBuilder = $this->buildFilteredQuery();

        $queryBuilder->select('COUNT(DISTINCT ' . $this->getDistinct() . ')');

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
        $data = $this->getDataQuery()
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }

    /**
     * Add the filter to the query corresponding to the search
     *
     * @param QueryBuilder $queryBuilder The query builder
     *
     * @return QueryBuilder
     */
    protected function buildSearch($queryBuilder)
    {
        $orderBy = [];

        foreach ($this->searches as $searches) {
            $where = [];
            foreach ($searches as $search) {
                $type = 'text';
                if (count($search) == 3) {
                    list($columns, $terms, $type) = $search;
                } else {
                    list($columns, $terms) = $search;
                }

                if ($type == 'text') {
                    if (is_array($terms)) {
                        $term = implode(' ', $terms);
                    } else {
                        $term = $terms;
                    }

                    list($cond, $order) = $this->search($term, $columns);

                    $orderBy[] = $order;
                    $where[] = $cond;
                }

                if ($type == 'dateRange') {
                    list($from, $to) = $terms;
                    foreach ($columns as $column) {
                        $conds = [];

                        if ($from) {
                            $conds[] = $column . ' >= ' . $this->connection->quote($from);
                        }
                        if ($to) {
                            $conds[] = $column . ' <= ' . $this->connection->quote($to);
                            $queryBuilder->andWhere($where);
                        }

                        if (!empty($conds)) {
                            $where[] = implode(' AND ', $conds);
                        }
                    }
                }
            }

            if (!empty($where)) {
                $queryBuilder->andWhere(implode(' OR ', $where));
            }
        }

        if (!empty($orderBy)) {
            $queryBuilder->addOrderBy(implode(' +', $orderBy), 'DESC');
        }
    }

    /**
     * Return the sort elements (WHERE et ORDER BY) for a search request
     *
     * @param string   $term    Term of search
     * @param string[] $columns Searchable table columns / sql expressions
     *
     * @return array
     */
    protected function search($term, $columns)
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
