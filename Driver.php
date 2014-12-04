<?php

namespace Solire\Trieur;

use Solire\Conf\Conf;

/**
 * Driver interface
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
interface Driver
{
    /**
     * Constructeur
     *
     * @param \Solire\Conf\Conf $config  The driver configuration
     * @param \Solire\Conf\Conf $columns The columns configuration
     */
    public function __construct(Conf $config, Conf $columns);

    /**
     * Set the request
     *
     * @param mixed $request The request
     *
     * @return void
     */
    public function setRequest($request);

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

    /**
     * Returns the response
     *
     * @param array $data          The data filtered by the current search,
     * offset and length
     * @param int   $count         The total of available lines filtered by the
     * current search
     * @param int   $filteredCount The total of available lines
     *
     * @return array
     */
    public function getResponse(array $data, $count, $filteredCount);
}
