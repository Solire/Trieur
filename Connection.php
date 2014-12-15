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
     * The columns configuration
     *
     * @var Conf
     */
    protected $columns = null;

    /**
     * The connection
     *
     * @var mixed
     */
    protected $connection;

    /**
     * An array of arrays where the first element is an array of columns or
     * expressions and the second element is an array of terms to look for
     *
     * @var array
     */
    protected $searches = [];

    /**
     * An associative array where keys are a sql column or expression and values
     * are a string 'ASC' or 'DESC'
     *
     * @var array
     */
    protected $order = [];

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
     * @param mixed $connection The connection
     * @param Conf  $conf       The configuration
     * @param Conf  $columns    The columns configuration
     */
    public function __construct(
        $connection,
        Conf $conf,
        Conf $columns
    ) {
        $this->connection = $connection;
        $this->conf       = $conf;
        $this->columns    = $columns;
    }

    /**
     * Sets the search
     *
     * @param array $searches An array of arrays where the first element is an array of columns or
     * expressions and the second element is an array of terms to look for
     *
     * @return void
     */
    public function addSearches($searches)
    {
        $this->searches = array_merge($this->searches, $searches);
    }

    /**
     * Add the search
     *
     * @param array $search An array where the first element is an array of
     * columns or expressions and the second element is an array of terms to
     * look for
     *
     * @return void
     */
    public function addSearch($search)
    {
        $this->searches[] = $search;
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
     * Sets the order
     *
     * @param array $order An array of two elements array where first element
     * is a column or expression and second element is a string 'ASC' or 'DESC'
     *
     * @return void
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Add an order
     *
     * @param string $column    A column
     * @param string $direction A direction string 'ASC' or 'DESC'
     *
     * @return void
     */
    public function addOrder($column, $direction = 'ASC')
    {
        $this->order[] = [$column, $direction];
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
