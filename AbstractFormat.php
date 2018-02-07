<?php

namespace Solire\Trieur;

use Solire\Conf\Conf;

/**
 * Data connection abstract class.
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
abstract class AbstractFormat
{
    /**
     * The columns conf.
     *
     * @var Conf
     */
    protected $conf;

    /**
     * The cell's value.
     *
     * @var mixed
     */
    protected $cell;

    /**
     * The value's row.
     *
     * @var mixed
     */
    protected $row;

    /**
     * Constructor.
     *
     * @param Conf  $conf Conf
     * @param array $row  Row
     * @param mixed $cell Cell
     */
    public function __construct(Conf $conf, $row, $cell)
    {
        $this->conf = $conf;
        $this->row = $row;
        $this->cell = $cell;

        $this->init();
    }

    /**
     * Initialize.
     *
     * @return void
     *
     * @throws Exception If the conf is invalid
     */
    abstract protected function init();

    /**
     * Returns formated value.
     *
     * @return mixed
     */
    abstract public function render();
}
