<?php
namespace Solire\Trieur;

use Solire\Conf\Conf;
use Exception;

/**
 * Columns configuration
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Columns implements \IteratorAggregate
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
     * Constructor
     *
     * @param Conf $columns Columns configuration
     */
    public function __construct(Conf $columns)
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
     * Get a column by its offset or name
     *
     * @param type $index Offset or name
     *
     * @return Conf
     * @throws Exception If the index is undefined
     */
    public function get($index)
    {
        if (isset($this->columnsByIndex[$index])) {
            return $this->columnsByIndex[$index];
        }

        if (isset($this->columnsByName[$index])) {
            return $this->columnsByName[$index];
        }

        throw new Exception('Undefined index "' . $index . '" in the columns list');
    }

    /**
     * Get a column attribut
     *
     * @param string|Conf $index        Column's index, name or the column object itself
     * @param array       $keys         Array of keys of the column configuration, the first founed
     * will be returned
     * @param string      $defaultValue If no keys where founed, then it returns
     * this value
     *
     * @return mixed
     * @throws Exception If none of the keys where founed, and no defaultValue
     * specified
     */
    public function getColumnAttribut($index, array $keys, $defaultValue = null)
    {
        if (is_object($index)) {
            $column = $index;
        } else {
            $column = $this->get($index);
        }

        foreach ($keys as $key) {
            if (isset($column->$key)) {
                return $column->$key;
            }
        }

        if ($defaultValue === null) {
            throw new Exception(
                'None of these indexes found "' . implode(',', $keys) . '" in the '
                . 'columns list'
            );
        }

        return $defaultValue;
    }

    /**
     * Return the column source parameter
     *
     * @param string|Conf $index Column's index, name or the column object itself
     * @param string      $key   Key of the column configuration, the first founed
     * will be returned
     *
     * @return mixed
     */
    public function getColumnSource($index, $key = null)
    {
        $keys = [
            'source',
            'name',
        ];
        if ($key !== null) {
            array_unshift($keys, $key);
        }
        return $this->getColumnAttribut($index, $keys);
    }

    /**
     * Return the column source sort parameter
     *
     * @param string|Conf $index Column's index, name or the column object itself
     *
     * @return mixed
     */
    public function getColumnSourceSort($index)
    {
        return $this->getColumnSource($index, 'sourceSort');
    }

    /**
     * Return the column source filter parameter
     *
     * @param string|Conf $index Column's index, name or the column object itself
     *
     * @return mixed
     */
    public function getColumnSourceFilter($index)
    {
        return $this->getColumnSource($index, 'sourceFilter');
    }

    /**
     * Return the column filter_type
     *
     * @param string|Conf $index Column's index, name or the column object itself
     *
     * @return string
     */
    public function getColumnFilterType($index)
    {
        return $this->getColumnAttribut($index, ['filterType'], 'Contain');
    }

    /**
     * Method making possible to iterate through the list of columns
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->columnsByIndex);
    }
}
