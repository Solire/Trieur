<?php
namespace Solire\Trieur;

use Solire\Conf\Conf;

/**
 * Datatables driver
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
abstract class Driver
{
    /**
     * List of columns with name index
     *
     * @var array
     */
    protected $columnsByName = [];

    /**
     * List of columns with numeric index
     *
     * @var array
     */
    protected $columnsByIndex = [];

    /**
     * The configuration
     *
     * @var Config
     */
    protected $config;

    /**
     * The request
     *
     * @var array
     */
    protected $request;

    /**
     * Constructeur
     *
     * @param Conf $config  The driver configuration
     * @param Conf $columns The columns configuration
     */
    public function __construct(Conf $config, Conf $columns)
    {
        $this->config = $config;
        $this->setColumns($columns);
    }

    /**
     * Set the request
     *
     * @param mixed $request The request
     *
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Set the columns
     *
     * @param Conf $columns The columns list
     *
     * @return void
     */
    protected function setColumns(Conf $columns)
    {
        $index = 0;
        foreach ($columns as $name => $column) {
            $column->name = $name;
            $this->columnsByIndex[$index] = $column;
            $this->columnsByName[$name] = $column;

            $index++;
        }
    }

    /**
     * Return the offset
     *
     * @return int
     */
    abstract public function offset();

    /**
     * Return the number of lines
     *
     * @return int
     */
    abstract public function length();

    /**
     * Return the order
     *
     * @return mixed
     */
    abstract public function order();

    /**
     * Get the column list (all or only the column that can be searched)
     *
     * @param bool   $searchable True to return only the searchable columns
     * @param string $connection If false returns for each column the entire
     * configuration, if true returns only the connection parameter for the
     * search
     *
     * @return array
     */
    abstract public function getColumns($searchable = false, $connection = false);

    /**
     * Return the filter term
     *
     * @return string
     */
    abstract public function getFilterTerm();

    /**
     * Return the filter terms for each columns
     *
     * @return array
     */
    abstract public function getFilterTermByColumns();

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
    abstract public function getResponse(array $data, $count, $filteredCount);
}
