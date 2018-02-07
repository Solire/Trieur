<?php

namespace Solire\Trieur;

use Solire\Conf\Conf;

/**
 * Format the data coming from the source.
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Format
{
    /**
     * Constructor.
     *
     * @param Columns $columns Columns list
     */
    public function __construct(Columns $columns)
    {
        $this->columns = $columns;
    }

    /**
     * Formate a source data.
     *
     * @param array $data The source data
     *
     * @return array
     */
    public function format($data)
    {
        $dataFormated = [];

        foreach ($data as $row) {
            $rowFormated = $this->formateRow($row);
            if ($rowFormated) {
                $dataFormated[] = $rowFormated;
            }
        }

        return $dataFormated;
    }

    /**
     * Formate a source row.
     *
     * @param array $row The source row
     *
     * @return type
     */
    protected function formateRow($row)
    {
        $rowFormated = [];
        foreach ($this->columns as $column) {
            if (isset($column->hide) && $column->hide) {
                continue;
            }

            $cellFormated = $this->formateCell($row, $column);
            $rowFormated[$column->name] = $cellFormated;
        }

        return $rowFormated;
    }

    /**
     * Formate a source cell.
     *
     * @param array $row    The source row
     * @param Conf  $column The cell's column
     *
     * @return string
     */
    protected function formateCell($row, Conf $column)
    {
        if (!isset($column->format)) {
            return $this->getCell($row, $column);
        }

        if (!isset($column->format->class)) {
            throw new Exception(
                sprintf(
                    'Undefined format class for column [%s]',
                    $column->name
                )
            );
        }

        $className = $this->getFormatClassName($column->format->class);

        if ($className === null) {
            throw new Exception(
                sprintf(
                    'Format class [%s] for column [%s] does not exist',
                    $column->format->class,
                    $column->name
                )
            );
        }

        $column->format->class = $className;

        if (!is_subclass_of($column->format->class, '\Solire\Trieur\AbstractFormat')) {
            throw new Exception(
                sprintf(
                    'Format class [%s] does not extend abstract class [%s]',
                    $column->format->class,
                    '\Solire\Trieur\AbstractFormat'
                )
            );
        }

        $formatInstance = new $column->format->class($column->format, $row, $this->getCell($row, $column));

        return $formatInstance->render();
    }

    /**
     * Get a source cell by its row and column.
     *
     * @param array $row    The source row
     * @param Conf  $column The cell's column
     *
     * @return string
     *
     * @throws Exception If the column index doesn't exist in the row
     */
    protected function getCell($row, Conf $column)
    {
        if (!isset($row[$column->sourceName])) {
            return '';
        }

        return $row[$column->sourceName];
    }

    /**
     * Returns the name of the filter class.
     *
     * @param string $formatType The filter type (Contain, DateRange etc.)
     *
     * @return string
     */
    private function getFormatClassName($formatType)
    {
        $className = $formatType;
        if (class_exists($className)) {
            return $className;
        }

        $r = new \ReflectionClass($this);
        $className = $r->getName() . '\\' . $formatType;
        if (class_exists($className)) {
            return $className;
        }

        return null;
    }
}
