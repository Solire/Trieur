<?php

namespace Solire\Trieur\Format;

use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use Solire\Trieur\AbstractFormat;

/**
 * Description of Function
 *
 * @author thansen
 */
class Callback extends AbstractFormat
{
    /**
     * The argument's array to pass to the callable
     *
     * @var array
     */
    private $arguments = [];

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        if (!isset($this->conf->name)) {
            throw new Exception('Missing output callback\'s name');
        }

        $callableName = $this->conf->name;
        if (!is_string($this->conf->name)) {
            $this->conf->name = array_values((array) $this->conf->name);
            $callableName = '(array) ' . implode('::', $this->conf->name);
        }

        if (!is_callable($this->conf->name)) {
            throw new Exception(
                sprintf(
                    'Callback [%s] does not exist',
                    $callableName
                )
            );
        }

        $parameters = $this->getParameters();

        $argumentsByName = [];
        if (isset($this->conf->arguments)) {
            $argumentsByName = $this->conf->arguments;
        }

        if (isset($this->conf->cell)) {
            $argumentsByName[$this->conf->cell] = $this->cell;
        }

        if (isset($this->conf->row)) {
            $argumentsByName[$this->conf->row] = $this->row;
        }

        $this->arguments = [];
        foreach ($parameters as $parameter) {
            if (!isset($argumentsByName[$parameter->name])) {
                if ($parameter->isOptional()) {
                    break;
                }

                throw new Exception(
                    sprintf(
                        'Missing argument [%s] for callback [%s]',
                        $parameter->name,
                        $callableName
                    )
                );
            }

            $this->arguments[] = $argumentsByName[$parameter->name];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return call_user_func_array($this->conf->name, $this->arguments);
    }

    /**
     * Get a callable arguments' name list
     *
     * @return ReflectionParameter[]
     */
    private function getParameters()
    {
        if (is_string($this->conf->name)) {
            return $this->getFunctionParameters($this->conf->name);
        }

        return $this->getMethodParameters((array) $this->conf->name);
    }

    /**
     * Get a function arguments' name list
     *
     * @param string $functionName The function name
     *
     * @return ReflectionParameter[]
     */
    private function getFunctionParameters($functionName)
    {
        $f = new ReflectionFunction($functionName);

        $parameters = [];
        foreach ($f->getParameters() as $param) {
            $parameters[] = $param;
        }

        return $parameters;
    }

    /**
     * Get a class method arguments' name list
     *
     * @param array $callable A callable array
     *
     * @return ReflectionParameter[]
     */
    private function getMethodParameters(array $callable)
    {
        list($className, $method) = $callable;

        $c = new ReflectionClass($className);
        $m = $c->getMethod($method);

        $parameters = [];
        foreach ($m->getParameters() as $param) {
            $parameters[] = $param;
        }

        return $parameters;
    }
}
