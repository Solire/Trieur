<?php
namespace Solire\Trieur;

/**
 * Description of SourceSearch
 *
 * @author thansen
 */
abstract class SourceSearch
{
    protected $columns;
    protected $terms;

    /**
     * Constructor
     *
     * @param mixed $columns The columns where to search
     * @param mixed $terms   The terms to look for
     */
    public function __construct($columns, $terms)
    {
        $this->columns = $columns;
        $this->terms = $terms;
    }

    /**
     * Filter
     *
     * @return void
     */
    abstract public function filter();
}
