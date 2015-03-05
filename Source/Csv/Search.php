<?php
namespace Solire\Trieur\Source\Csv;

use Solire\Trieur\SourceSearch;

/**
 * Csv abstract search class
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
    protected $row;

    /**
     * Sets the row
     *
     * @param array $row The row
     *
     * @return void
     */
    public function setRow(array $row)
    {
        $this->row = $row;
    }
}
