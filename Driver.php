<?php

namespace Solire\Trieur;

/**
 * Driver interface
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
interface Driver
{
    /**
     * Constructor
     *
     * @param \Solire\Conf\Conf $config  The driver configuration
     * @param \Solire\Conf\Conf $columns The columns configuration
     */
    public function __construct($config, $columns);

    /**
     * Set the request
     *
     * @param array $request The request
     *
     * @return void
     */
    public function setRequest(array $request);

    /**
     * Return the offset
     *
     * @return int
     */
    public function offset();

    /**
     * Return the number of lines
     *
     * @return int
     */
    public function length();

    /**
     * Return the order
     *
     * @return mixed
     */
    public function order();

    /**
     * Return the filter term
     *
     * @return string
     */
    public function getFilterTerm();

    /**
     * Return the searchable columns
     *
     * @return array
     */
    public function getSearchableColumns();

    /**
     * Return the filter terms for each columns
     *
     * @return array
     */
    public function getFilterTermByColumns();
}
