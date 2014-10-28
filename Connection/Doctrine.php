<?php

namespace Solire\Trieur\Connection;

use \Solire\Trieur\Connection;
use \Solire\Trieur\Driver;
use \Solire\Trieur\Config;

use \Doctrine\DBAL\Connection as DoctrineConnection;

/**
 * Doctrine connection wrapper
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Doctrine implements Connection
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
     * @var Config
     */
    protected $config;

    /**
     * A doctrine query builder
     *
     * @var \Doctrine\DBAL\Query\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Constructor
     *
     * @param \Doctrine\DBAL\Connection $connection The connection
     * @param \Solire\Trieur\Driver     $driver     The driver
     * @param \Solire\Trieur\Config     $config     The configuration
     */
    public function __construct(
        $connection,
        Driver $driver,
        Config $config
    ) {
        $this->connection = $connection;
        $this->driver     = $driver;
        $this->config     = $config;
    }

    /**
     * Builds the raw query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function buildRawQuery()
    {
        $this->queryBuilder = $this->connection->createQueryBuilder();

        $this->queryBuilder->select($this->config->getConfig('sql', 'select'));

        /*
         * Main table
         */
        $from = $this->config->getConfig('sql', 'from');
        list($fromTable, $fromAlias) = explode('|', $from);
        $this->queryBuilder->from($fromTable, $fromAlias);

        /*
         * Inner join
         */
        $joins = $this->config->getConfig('sql', 'join');
        if (!empty($joins)) {
            if (!is_array($joins)) {
                $joins = array($joins);
            }

            foreach ($joins as $join) {
                list($table, $alias, $condition) = explode('|', $join);
                $this->queryBuilder->innerJoin($fromAlias, $table, $alias, $condition);
            }
        }

        /*
         * Left joins
         */
        $joins = $this->config->getConfig('sql', 'leftJoin');
        if (!empty($joins)) {
            if (!is_array($joins)) {
                $joins = array($joins);
            }

            foreach ($joins as $join) {
                list($table, $alias, $condition) = explode('|', $join);
                $this->queryBuilder->leftJoin($fromAlias, $table, $alias, $condition);
            }
        }

        /*
         * Right joins
         */
        $joins = $this->config->getConfig('sql', 'rightJoin');
        if (!empty($joins)) {
            if (!is_array($joins)) {
                $joins = array($joins);
            }

            foreach ($joins as $join) {
                list($table, $alias, $condition) = explode('|', $join);
                $this->queryBuilder->rightJoin($fromAlias, $table, $alias, $condition);
            }
        }

        /*
         * Condition
         */
        $wheres = $this->config->getConfig('sql', 'where');
        if (!empty($wheres)) {
            if (!is_array($wheres)) {
                $wheres = array($wheres);
            }
            foreach ($wheres as $where) {
                $this->queryBuilder->andWhere($where);
            }
        }
    }

    /**
     * Build the filtered query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function buildFilteredQuery()
    {
        $this->buildRawQuery();

        $term    = $this->driver->getFilterTerm();
        if (!empty($term)) {
            $columns = $this->driver->getSearchableColumns();
            list($where, $order) = $this->search($term, $columns);
            $this->queryBuilder->andWhere($where);
            $this->queryBuilder->addOrderBy($order, 'DESC');
        }
    }

    /**
     * Build the data query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getDataQuery()
    {
        $this->buildFilteredQuery();

        $this->queryBuilder->setFirstResult($this->driver->offset());
        $this->queryBuilder->setMaxResults($this->driver->length());

        $orders = $this->driver->order();
        foreach ($orders as $order) {
            list($col, $dir) = $order;
            $this->queryBuilder->addOrderBy($col, $dir);
        }

        return $this->queryBuilder;
    }

    /**
     * Build the count of raw query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getRawCountQuery()
    {
        $this->buildRawQuery();
        $this->queryBuilder->select('COUNT(DISTINCT ' . $this->config->getConfig('sql', 'group') . ')');

        return $this->queryBuilder;
    }

    /**
     * Build the count of filtered query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getFilteredCountQuery()
    {
        $this->buildFilteredQuery();
        $this->queryBuilder->select('COUNT(DISTINCT ' . $this->config->getConfig('sql', 'group') . ')');

        return $this->queryBuilder;
    }

    /**
     * Return the sort elements (WHERE et ORDER BY) for a search request
     *
     * @param string   $term    Term of search
     * @param string[] $columns Columns in where to search
     *
     * @return array
     */
    public function search($term, $columns)
    {
        /**
         * Variable qui contient la chaine de recherche
         */
        $stringSearch = trim($term);

        /**
         * On divise en mots (séparé par des espace)
         */
        $words = preg_split('`\s+`', $stringSearch);

        if (count($words) > 1) {
            array_unshift($words, $stringSearch);
        }

        $filterWords = array();
        $orderBy     = array();
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

        return array(
            ' (' . implode(' OR ', $filterWords) . ')',
            ' ' . implode(' + ', $orderBy),
        );
    }
}
