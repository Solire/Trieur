<?php

namespace Solire\Trieur;

/**
 * Config
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Config
{
    protected $config = array();

    protected $sqlConfig = array();

    protected $driverName;

    protected $columns = array();

    protected $columnsMap = array();

    /**
     * Constructor
     *
     * @param array $config The configuration
     */
    public function __construct($config)
    {
        $ind = 0;
        foreach ($config as $section => $column) {
            if (substr($section, 0, 2) == '__') {
                $this->config[substr($section, 2)] = $column;
            } else {
                $column['name'] = $section;
                $this->columns[$ind] = $column;
                $this->columnsMap[$section] = $ind;
                $ind++;
            }
        }
    }

    /**
     * Return the list of columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Return a column by its name or index in the columns list
     *
     * @param string|int  $name Name or index (beginning at 0) of the column
     * @param string|null $key  Column's attribute's name or null to get the
     * entire column's configuration
     *
     * @return mixed
     */
    public function getColumn($name, $key = null)
    {
        $index = $name;
        if (!is_numeric($name)) {
            $index = $this->columnsMap[$name];
        }

        $column = $this->columns[$index];

        if ($key === null) {
            return $column;
        }

        return $column[$key];
    }

    /**
     * Set the driver's name
     *
     * @param string $name The driver's name
     *
     * @return void
     */
    public function setDriverName($name)
    {
        $this->driverName = $name;
    }

    /**
     * Return the driver configuration
     *
     * @param string $key The attribute's name
     *
     * @return array
     */
    public function getDriverConfig($key = null)
    {
        return $this->getConfig($this->driverName, $key);
    }

    /**
     * Return a configuration section by its name
     *
     * @param string $name The configuration's name
     * @param string $key  The attrbiute's name
     *
     * @return mixed
     */
    public function getConfig($name, $key = null)
    {
        if (!isset($this->config[$name])) {
            return null;
        }

        if ($key === null) {
            return $this->config[$name];
        }

        if (!isset($this->config[$name][$key])) {
            return null;
        }

        return $this->config[$name][$key];
    }
}
