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
     * @param array $searches An array of arrays where the first element is an
     * array of columns or expressions and the second element is an array of
     * terms to look for
     *
     * @return void
     */
    final public function setSearches($searches)
    {
        $this->searches = [];
        $this->addSearches($searches);
    }

    /**
     * Add multiple searches
     *
     * @param array $searches An array of arrays where the first element is an
     * array of columns or expressions and the second element is an array of
     * terms to look for
     *
     * @return void
     */
    final public function addSearches($searches)
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
    final public function addSearch($search)
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
     * Sets the order
     *
     * @param array $orders An array of two elements array where first element
     * is a column or expression and second element is a string 'ASC' or 'DESC'
     *
     * @return void
     */
    final public function setOrders($orders)
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
    final public function addOrder($column, $direction = 'ASC')
    {
        if (!is_object($column)) {
            $column = $this->columns->get($column);
        }
        $this->orders[] = [$column, $direction];
    }

    /**
     * Adds the différent search filter
     *
     * @return boolean
     */
    final public function search()
    {
        $itsAMatch = true;

        foreach ($this->searches as $search)
        {
            list($columns, $term, $searchType) = $search;

            if (empty($columns)) {
                continue;
            }

            $search = $this->instantiateSearch($columns, $term, $searchType);
            $status = $this->processSearch($search);

            if (!$status) {
                $itsAMatch = false;
            }
        }

        return $itsAMatch;
    }

    /**
     * Instantiate an object to do the search
     *
     * @param array  $columns    Array of columns
     * @param mixed  $term       The term(s) we're looking for
     * @param string $searchType The search type
     *
     * @return SourceSearch
     */
    private function instantiateSearch($columns, $term, $searchType)
    {
        $className = $this->getSearchClassName($searchType);

        return new $className($columns, $term);

    }

    /**
     * Returns the name of the search class
     *
     * @param string $searchType The search type (Contain, DateRange etc.)
     *
     * @return string
     * @throws Exception
     */
    private function getSearchClassName($searchType)
    {
        $className = $searchType;
        if (class_exists($className)) {
            return $className;
        }

        $r = new \ReflectionClass($this);
        $className = $r->getName() . '\\' . $searchType;
        if (class_exists($className)) {
            return $className;
        }

        throw new Exception(
            sprintf('No search class found for type [%s]', $searchType)
        );
    }

    /**
     * Do the search and returns true if it's a success, false otherwise
     *
     * @param SourceSearch $filter The search object
     *
     * @return bool
     */
    abstract protected function processSearch(SourceSearch $filter);

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
