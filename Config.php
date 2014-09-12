<?php

namespace Solire\Trieur;

class Config
{
    protected $config = array();

    protected $sqlConfig = array();

    protected $driverName;

    protected $columns = array();

    protected $columnsMap = array();

    /**
     *
     *
     * @param array $config
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
     * Renvoi le tableau des colonnes
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Renvoi une colonne déterminé par son index ou son nom
     *
     * @param string|int  $name nom ou index (à partir de 0) de la colonne
     * @param string|null $key  nom de l'attribut de la colonne ou null pour
     * récupérer le tableau de config entier de la colonne
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

    public function setDriverName($name)
    {
        $this->driverName = $name;
    }

    public function getDriverConfig($key = null)
    {
        return $this->getConfig($this->driverName, $key);
    }

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
