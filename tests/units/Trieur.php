<?php
namespace Solire\Trieur\test\units;

use \atoum as Atoum;
use Solire\Trieur\Trieur as TestClass;

use Solire\Conf\Conf;

class Trieur extends Atoum
{
    public function testConstruct()
    {
        $conf = new Conf;

        $this
            ->exception(function()use($conf){
                new TestClass($conf);
            })
            ->hasMessage('No class for driver class founed or given')
        ;

        $conf->driver = new Conf;
        $conf->driver->class = '\Solire\Trieur\Driver\DataTables';
        $conf->driver->conf = new Conf;

        $conf->columns = new Conf;

        $this
            ->object(
                new TestClass($conf)
            )
        ;
    }
}
