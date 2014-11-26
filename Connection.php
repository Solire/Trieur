<?php

namespace Solire\Trieur;

use Solire\Conf\Conf;

/**
 * Data connection interface
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
interface Connection
{
    /**
     * Constructor
     *
     * @param mixed                 $connection The connection
     * @param \Solire\Trieur\Driver $driver     The driver
     * @param Conf                  $conf       The configuration
     */
    public function __construct(
        $connection,
        Driver $driver,
        Conf $conf
    );

    /**
     * Return the total of available lines
     *
     * @return int Total number
     */
    public function getCount();

    /**
     * Return the total of available lines filtered by the current search
     *
     * @return int Total number
     */
    public function getFilteredCount();

    /**
     * Returns the data filtered by the current search
     *
     * @return mixed
     */
    public function getData();
}
