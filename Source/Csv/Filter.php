<?php
namespace Solire\Trieur\Source\Csv;

use Solire\Trieur\SourceFilter;

/**
 * Csv abstract filter class
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
abstract class Filter extends SourceFilter
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
