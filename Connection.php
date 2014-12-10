<?php
namespace Solire\Trieur;

use Solire\Conf\Conf;

/**
 * Data connection abstract class
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
abstract class Connection
{
    /**
     * The configuration
     *
     * @var Conf
     */
    protected $conf = null;

    /**
     * The database doctrine connection
     *
     * @var DoctrineConnection
     */
    protected $connection;

    /**
     * An associative array where keys are a sql column or expression and values
     * are an array of terms to look for
     *
     * @var array
     */
    protected $search = null;

    /**
     * An associative array where keys are a sql column or expression and values
     * are a string 'ASC' or 'DESC'
     *
     * @var array
     */
    protected $order = null;

    /**
     * Offset of the query
     *
     * @var int
     */
    protected $offset = null;

    /**
     * Length of the query
     *
     * @var int
     */
    protected $length = null;

    /**
     * Constructor
     *
     * @param mixed                 $connection The connection
     * @param \Solire\Trieur\Driver $driver     The driver
     * @param Conf                  $conf       The configuration
     */
    public function __construct(
        $connection,
        Conf $conf
    ) {
        $this->connection = $connection;
        $this->conf       = $conf;
    }

    /**
     * Sets the search
     *
     * @param array $search An associative array where keys are a sql column or
     * expression and values are an array of terms to look for
     *
     * @return void
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }

    /**
     * Sets the offset
     *
     * @param int $offset Offset of the query
     *
     * @return void
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Sets the length
     *
     * @param int $length Length of the query
     *
     * @return void
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * Return the total of available lines
     *
     * @return int Total number
     */
    abstract public function getCount();

    /**
     * Return the total of available lines filtered by the current search
     *
     * @return int Total number
     */
    abstract public function getFilteredCount();

    /**
     * Returns the data filtered by the current search
     *
     * @return mixed
     */
    abstract public function getData();
}
