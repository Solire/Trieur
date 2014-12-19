<?php
namespace Solire\Trieur;

use Solire\Trieur\Columns;
use Solire\Conf\Conf;

/**
 * Data connection abstract class
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
abstract class Source
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
     * @var Columns
     */
    protected $columns = null;

    /**
     * The connection
     *
     * @var mixed
     */
    protected $connection = null;

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
    protected $orders = [];

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
     * @param mixed   $connection The connection
     * @param Conf    $conf       The configuration
     * @param Columns $columns    The columns configuration
     */
    public function __construct(
        Conf $conf,
        Columns $columns,
        $connection = null
    ) {
        $this->conf       = $conf;
        $this->columns    = $columns;
        $this->connection = $connection;
    }

    /**
     * Set the searches
     *
     * @param array $searches An array of arrays where the first element is an array of columns or
     * expressions and the second element is an array of terms to look for
     *
     * @return void
     */
    public function setSearches($searches)
    {
        $this->searches = [];
        $this->addSearches($searches);
    }

    /**
     * Add multiple searches
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
     * @param array $orders An array of two elements array where first element
     * is a column or expression and second element is a string 'ASC' or 'DESC'
     *
     * @return void
     */
    public function setOrders($orders)
    {
        $this->orders = [];
        foreach ($orders as $order) {
            list($column, $dir) = $order;
            $this->addOrder($column, $dir);
        }
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
        if (!is_object($column)) {
            $column = $this->columns->get($column);
        }
        $this->orders[] = [$column, $direction];
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
