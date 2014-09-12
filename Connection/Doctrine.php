<?php

namespace Solire\Trieur\Connection;

use \Solire\Trieur\Connection,
    \Doctrine\DBAL\Connection as DoctrineConnection,
    \Solire\Trieur\Driver,
    \Solire\Trieur\Config;

class Doctrine implements Connection
{
    /**
     *
     *
     * @var DoctrineConnection
     */
    protected $connection;

    /**
     *
     *
     * @var Driver
     */
    protected $driver;

    /**
     *
     *
     * @var Config
     */
    protected $config;

    /**
     *
     *
     * @var \Doctrine\DBAL\Query\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Constructeur
     *
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Solire\Trieur\Driver     $driver
     * @param \Solire\Trieur\Config     $config
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
     *
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function buildRawQuery()
    {
        $this->queryBuilder = $this->connection->createQueryBuilder();

        $this->queryBuilder->select($this->config->getConfig('sql', 'select'));

        $from = $this->config->getConfig('sql', 'from');
        list($fromTable, $fromAlias) = explode('|', $from);
        $this->queryBuilder->from($fromTable, $fromAlias);

        $joins = $this->config->getConfig('sql', 'join');
        if (!empty($joins)) {
            if (!is_array($joins)) {
                $joins = array($joins);
            }
            foreach ($joins as $join) {
                list($table, $alias, $condition) = explode('|', $join);
                $this->queryBuilder->join($fromAlias, $table, $alias, $condition);
            }
        }

        $joins = $this->config->getConfig('sql', 'leftJoin');
        if (!empty($joins)) {
            if (!is_array($joins)) {
                $joins = array($joins);
            }
            foreach ($joins as $join) {
                list($table, $alias, $condition) = explode('|', $join);
                $this->queryBuilder->join($fromAlias, $table, $alias, $condition);
            }
        }

        $wheres = $this->config->getConfig('sql', 'where');
        if (!empty($wheres)) {
            if (!is_array($wheres)) {
                $wheres = array($wheres);
            }
            foreach ($wheres as $where) {
                list($table, $alias, $condition) = explode('|', $join);
                $this->queryBuilder->join($fromAlias, $table, $alias, $condition);
            }
        }
    }

    /**
     *
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function buildFilteredQuery()
    {
        $this->queryBuilder = $this->buildRawQuery();

        $term    = $this->driver->getFilterTerm();
        if (!empty($term)) {
            $columns = $this->driver->getSearchableColumns();
            list($where, $order) = $this->search($term, $columns);
            $this->queryBuilder->andWhere($where);
            $this->queryBuilder->addOrderBy($order, 'DESC');
        }
    }

    /**
     *
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

    public function getRawCountQuery()
    {
        $this->buildRawQuery();
        $this->queryBuilder->select('COUNT(DISTINCT' . $this->config->getConfig('sql', 'group') . ')');

        return $this->queryBuilder;
    }

    public function getFilteredCountQuery()
    {
        $this->buildFilteredQuery();
        $this->queryBuilder->select('COUNT(DISTINCT' . $this->config->getConfig('sql', 'group') . ')');

        return $this->queryBuilder;
    }

    /**
     * Retourne les éléments de tri (WHERE et ORDER BY) pour la requête de
     * recherche en fonction d'un terme de recherche
     *
     * @param string   $term
     * @param string[] $columns
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
