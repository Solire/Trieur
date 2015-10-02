<?php
namespace Solire\Trieur;

use Solire\Trieur\Columns;
use Solire\Conf\Conf;

/**
 * Data connection abstract class
 *
 * @author  thansen <thansen@solire.fr>
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
    protected $filters = [];

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
     * @param Conf    $conf       The configuration
     * @param Columns $columns    The columns configuration
     * @param mixed   $connection The connection
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
     * Set the filters
     *
     * @param array $filters An array of filters, a filter being an array where
     * - the first element is an (array of) columns or expressions
     * - the second element is an (array of) terms to look for
     * - the third element is the filter type (example : Contain)
     *
     * @return void
     */
    final public function setFilters($filters)
    {
        $this->filters = [];
        $this->addFilters($filters);
    }

    /**
     * Add multiple filters
     *
     * @param array $filters An array of filters, a filter being an array where
     * - the first element is an (array of) columns or expressions
     * - the second element is an (array of) terms to look for
     * - the third element is the filter type (example : Contain)
     *
     * @return void
     */
    final public function addFilters($filters)
    {
        $this->filters = array_merge($this->filters, $filters);
    }

    /**
     * Add a filter
     *
     * @param array $filter An array where
     * - the first element is an (array of) columns or expressions
     * - the second element is an (array of) terms to look for
     * - the third element is the filter type (example : Contain)
     *
     * @return void
     */
    final public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Sets the offset
     *
     * @param int $offset Offset of the query
     *
     * @return void
     */
    final public function setOffset($offset)
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
    final public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * Sets orders
     *
     * @param array $orders An array of two elements array where first element
     * is a column or expression and second element is a string 'ASC' or 'DESC'
     *
     * @return void
     */
    final public function setOrders($orders)
    {
        $this->orders = [];
        $this->addOrders($orders);
    }

    /**
     * Add orders
     *
     * @param array $orders An array of two elements array where first element
     * is a column or expression and second element is a string 'ASC' or 'DESC'
     *
     * @return void
     */
    final public function addOrders($orders)
    {
        foreach ($orders as $order) {
            list($column, $dir) = $order;
            $this->addOrder($column, $dir);
        }
    }

    /**
     * Add an order
     *
     * @param string|Column $column    A column
     * @param string        $direction A direction string 'ASC' or 'DESC'
     *
     * @return void
     */
    final public function addOrder($column, $direction = 'ASC')
    {
        if (!is_object($column)) {
            $column = $this->columns->get($column);
        }

        $this->orders[] = [
            $column,
            $direction
        ];
    }

    /**
     * Adds the different filters
     *
     * @return boolean
     */
    final public function filter()
    {
        $itsAMatch = true;

        foreach ($this->filters as $filter) {
            list($columns, $term, $filterType) = $filter;

            if (empty($columns)) {
                continue;
            }

            $filter = $this->instantiateFilter($columns, $term, $filterType);
            $status = $this->processFilter($filter);

            if (!$status) {
                $itsAMatch = false;
            }
        }

        return $itsAMatch;
    }

    /**
     * Instantiate an object to process the filter
     *
     * @param array  $columns    Array of columns
     * @param mixed  $term       The term(s) we're looking for
     * @param string $filterType The filter type
     *
     * @return SourceFilter
     */
    private function instantiateFilter($columns, $term, $filterType)
    {
        $className = $this->getFilterClassName($filterType);

        return new $className($columns, $term);

    }

    /**
     * Returns the name of the filter class
     *
     * @param string $filterType The filter type (Contain, DateRange etc.)
     *
     * @return string
     * @throws Exception
     */
    private function getFilterClassName($filterType)
    {
        $className = $filterType;
        if (class_exists($className)) {
            return $className;
        }

        $r = new \ReflectionClass($this);
        $className = $r->getName() . '\\' . $filterType;
        if (class_exists($className)) {
            return $className;
        }

        throw new Exception(
            sprintf('No filter class found for type [%s]', $filterType)
        );
    }

    /**
     * Do the filter and returns true if it's a success, false otherwise
     *
     * @param SourceFilter $filter The filter object
     *
     * @return bool
     */
    abstract protected function processFilter(SourceFilter $filter);

    /**
     * Return the total of available lines
     *
     * @return int Total number
     */
    abstract public function getCount();

    /**
     * Return the total of available lines filtered by the current filters
     *
     * @return int Total number
     */
    abstract public function getFilteredCount();

    /**
     * Returns the data filtered by the current filters
     *
     * @return mixed
     */
    abstract public function getData();
}
