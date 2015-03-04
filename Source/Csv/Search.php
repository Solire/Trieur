<?php
namespace Solire\Trieur\Source\Csv;

use Solire\Trieur\SourceSearch;

/**
 * Description of Search
 *
 * @author thansen
 */
abstract class Search extends SourceSearch
{
    /**
     * QueryBuilder
     *
     * @var QueryBuilder
     */
    protected $row;

    /**
     * Sets the query builder
     *
     * @param QueryBuilder $queryBuilder The querybuilder
     *
     * @return void
     */
    public function setRow(array $row)
    {
        $this->row = $row;
    }
}
