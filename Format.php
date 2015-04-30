<?php

namespace Solire\Trieur;

use Solire\Conf\Conf;

/**
 * Description of Format
 *
 * @author thansen
 */
class Format
{
    /**
     * Constructor
     *
     * @param Columns $columns Columns list
     */
    public function __construct(Columns $columns)
    {
        $this->columns = $columns;
    }

    /**
     * Formate a source data
     *
     * @param array $data The source data
     *
     * @return array
     */
    public function format($data)
    {
        $dataFormated = array();

        foreach ($data as $row) {
            $rowFormated = $this->formateRow($row);
            if ($rowFormated) {
                $dataFormated[] = $rowFormated;
            }
        }

        return $dataFormated;
    }

    /**
     * Formate a source row
     *
     * @param array $row The source row
     *
     * @return type
     */
    protected function formateRow($row)
    {
        $rowFormated = array();
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
     * Formate a source cell
     *
     * @param array $row    The source row
     * @param Conf  $column The cell's column
     *
     * @return string
     */
    protected function formateCell($row, Conf $column)
    {
        if (isset($column->view)) {
            return $this->view($row, $column);
        }

        if (isset($column->callback)) {
            return $this->callBack($row, $column);
        }

        return $row[$column->sourceName];
    }

    /**
     * Return the content of a view
     *
     * @param array $row    The source row
     * @param Conf  $column The cell's column
     *
     * @return string
     * @throws Exception If the view file doesn't exist
     */
    protected function view($row, Conf $column)
    {
        ob_start();

        if (!file_exists($column->view)
            || !is_readable($column->view)
        ) {
            $message = sprintf(
                'The view file "%s" does not exist or is not readable',
                $column->view
            );
            throw new Exception($message);
        }

        include $column->view;
        return ob_get_clean();
    }

    /**
     * Return the result of a callback function
     *
     * @param array $row    The source row
     * @param Conf  $column The cell's column
     *
     * @return string
     */
    protected function callBack($row, Conf $column)
    {
        $cell = $row[$column->sourceName];
        $function = $column->callback;

        $arguments = [];

        if (is_string($function)) {
            $functionName = $function;
            $arguments[] = $cell;
        } else {
            $functionName = $function->name;

            if (isset($function->arguments)) {
                $arguments = (array) $function->arguments;
            }

            if (isset($function->cell)) {
                $this->insertToArray($arguments, $cell, $function->cell);
            }

            if (isset($function->row)) {
                $this->insertToArray($arguments, $row, $function->row);
            }
        }

        $t = explode('::', $functionName);

        if (count($t) > 1) {
            list($class, $methodName) = $t;
            $exists = method_exists($class, $methodName);
        } else {
            $exists = function_exists($functionName) || is_callable($functionName);
        }

        if (!$exists) {
            $message = sprintf(
                'The function "%s" does not exist',
                $functionName
            );
            throw new Exception($message);
        }

        return call_user_func_array($functionName, $arguments);
    }

    /**
     * Inserts a row in an array in a given offset (numeric offset only)
     *
     * @param array $array  The array
     * @param mixed $row    The row to insert
     * @param int   $offset The offset
     *
     * @return void
     */
    protected function insertToArray(&$array, $row, $offset)
    {
        if ($offset >= count($array)) {
            $array[$offset] = $row;
            return;
        }

        $array = array_merge(
            array_slice($array, 0, $offset),
            [$row],
            array_slice($array, $offset)
        );
    }
}
