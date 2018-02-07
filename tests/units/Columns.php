<?php

namespace Solire\Trieur\test\units;

use atoum;
use Solire\Conf\Loader;

/**
 * Description of Columns.
 *
 * @author thansen
 */
class Columns extends atoum
{
    public function testConstructor01()
    {
        $conf = Loader::load([
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
            ->if($columns = $this->newTestedInstance($conf))
            ->object($columns)
                ->isInstanceOf('\Solire\Trieur\Columns')
        ;
    }

    public function testGet()
    {
        $col03 = Loader::load([
            'attr01' => 'value.03.01',
            'attr02' => 'value.03.02',
            'source' => 'source.03',
            'sourceSort' => 'sourceSort.03',
            'sourceFilter' => 'sourceFilter.03',
            'filterType' => 'select',
        ]);

        $conf = Loader::load([
            'offset01' => [
                'attr01' => 'value.01.01',
                'attr02' => 'value.01.02',
            ],
            'offset02' => [
                'attr01' => 'value.02.01',
                'attr02' => 'value.02.02',
                'source' => 'source.02',
            ],
            'offset03' => $col03,
        ]);

        $this
            ->if($columns = $this->newTestedInstance($conf))
            ->object($columns->get(0))
                ->isInstanceOf('\Solire\Conf\Conf')
            ->object($columns->get('offset01'))
                ->isInstanceOf('\Solire\Conf\Conf')
            ->exception(function () use ($columns) {
                $columns->get('wrongIndex');
            })
                ->isInstanceOf('\Exception')
                ->hasMessage('Undefined index "wrongIndex" in the columns list')
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
