<?php
namespace Solire\Trieur\Source\Doctrine;

use Solire\Trieur\SourceSearch;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Doctrine abstract search class
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
abstract class Search extends SourceSearch
{
    /**
     * QueryBuilder
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Sets the query builder
     *
     * @param QueryBuilder $queryBuilder The querybuilder
     *
     * @return void
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
}
