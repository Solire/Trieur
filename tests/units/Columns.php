<?php
namespace Solire\Trieur\test\units;

use \atoum as Atoum;
use Solire\Trieur\Columns as TestClass;

use Solire\Conf\Conf;

/**
 * Description of Columns
 *
 * @author thansen
 */
class Columns extends Atoum
{
    public function testConstructor01()
    {
        $conf = arrayToConf([
            'offset01' => [
                'attr01' => 'value.01.01',
                'attr02' => 'value.01.02',
            ],
            'offset02' => [
                'attr01' => 'value.02.01',
                'attr02' => 'value.02.02',
            ],
        ]);

        $this
            ->if($columns = new TestClass($conf))
            ->object($columns)
                ->isInstanceOf('\Solire\Trieur\Columns')
        ;
    }

    public function testGet()
    {
        $conf = arrayToConf([
            'offset01' => [
                'attr01' => 'value.01.01',
                'attr02' => 'value.01.02',
            ],
            'offset02' => [
                'attr01' => 'value.02.01',
                'attr02' => 'value.02.02',
                'source' => 'source.02',
            ],
            'offset03' => [
                'attr01' => 'value.03.01',
                'attr02' => 'value.03.02',
                'source' => 'source.03',
                'sourceFilter' => 'sourceFilter.03',
                'sourceSort' => 'sourceSort.03',
            ],
        ]);

        $this
            ->if($columns = new TestClass($conf))
            ->object($column = $columns->get(0))
                ->isInstanceOf('\Solire\Conf\Conf')
            ->object($column = $columns->get('offset01'))
                ->isInstanceOf('\Solire\Conf\Conf')
            ->exception(function()use($columns){
                $columns->get('wrongIndex');
            })
                ->isInstanceOf('\Exception')
                ->hasMessage('Undefined index "wrongIndex" in the columns list')
            ->exception(function()use($columns){
                $columns->getColumnAttribut('offset01', ['a', 'b', 'c']);
            })
                ->isInstanceOf('\Exception')
                ->hasMessage('None of these indexes found "a,b,c" in the columns list')
            ->string($columns->getColumnAttribut('offset01', ['attr01']))
                ->isEqualTo('value.01.01')
            ->string($columns->getColumnAttribut('offset01', ['attr02', 'attr01']))
                ->isEqualTo('value.01.02')
            ->string($columns->getColumnAttribut('offset01', ['attr01', 'attr02']))
                ->isEqualTo('value.01.01')
            ->string($columns->getColumnAttribut('offset01', ['attr00', 'attr01']))
                ->isEqualTo('value.01.01')

            ->string($columns->getColumnSource('offset01'))
                ->isEqualTo('offset01')
            ->string($columns->getColumnSourceFilter('offset02'))
                ->isEqualTo('source.02')
            ->string($columns->getColumnSourceSort('offset03'))
                ->isEqualTo('sourceSort.03')
        ;

        $keys = range(0, 2);
        foreach ($columns as $key => $column) {
            $this
                ->integer($key)
                    ->isEqualTo(array_shift($keys))
                ->object($column)
                ->isInstanceOf('\Solire\Conf\Conf')
            ;
        }
    }
}
