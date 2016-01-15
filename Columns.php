<?php
namespace Solire\Trieur;

use ArrayIterator;
use IteratorAggregate;
use Solire\Conf\Conf;

/**
 * Columns configuration
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Columns implements IteratorAggregate
{
    protected static $fields = [
        'label' => [
            'fields' => [
                'name',
            ],
        ],
        'source' => [
            'fields' => [
                'name',
            ],
        ],
        'sourceName' => [
            'fields' => [
                'name',
            ],
        ],
        'sourceSort' => [
            'fields' => [
                'source',
            ],
        ],
        'sourceFilter' => [
            'fields' => [
                'source',
            ],
        ],
        'driverFilterType' => [
            'fields' => [
                'filterType',
            ],
            'default' => 'text',
        ],
        'sourceFilterType' => [
            'fields' => [
                'filterType',
            ],
            'default' => 'Contain',
        ],
    ];

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
            $this->buildColumnConf($name, $column);
            $this->columnsByIndex[$index] = $column;
            $this->columnsByName[$name] = $column;

            $index++;
        }
    }

    /**
     * Build / complete the configuration of a column
     *
     * @param type $name   The name of the column
     * @param Conf $column The column configuration
     *
     * @return void
     */
    protected function buildColumnConf($name, Conf $column)
    {
        $column->name = $name;

        foreach (self::$fields as $fieldName => $defaults) {
            if ($column->has($fieldName)) {
                continue;
            }

            foreach ($defaults['fields'] as $field) {
                if ($column->has($field)) {
                    $column->set($column->get($field), $fieldName);
                    break;
                }
            }

            if ($column->has($fieldName)) {
                continue;
            }

            $column->set($defaults['default'], $fieldName);
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
     * Method making possible to iterate through the list of columns
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->columnsByIndex);
    }
}
