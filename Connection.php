<?php

namespace Solire\Trieur;

/**
 * Database connection interface
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
interface Connection
{
    /**
     * Constructeur
     *
     * @param mixed                 $connection The connection
     * @param \Solire\Trieur\Driver $driver     The driver
     * @param \Solire\Conf\Conf     $conf       The configuration
     */
    public function __construct($connection, Driver $driver, $conf);
}
