<?php

namespace Solire\Trieur\test\units\Format;

use atoum;
use Solire\Conf\Conf;

class Callback extends atoum
{
    public function testConstruct1()
    {
        $conf = new Conf();
        $value = $row = null;

        $this
            ->exception(function()use($conf, $row, $value){
                $this->newTestedInstance($conf, $row, $value);
            })
                ->isInstanceOf('Exception')
                ->hasMessage('Missing output callback\'s name')
        ;
    }

    public function testConstruct2()
    {
        $conf = new Conf();
        $conf->name = 'trimZ';
        $value = ' aa aa ';
        $row = null;
        $this
            ->exception(function()use($conf, $row, $value){
                $this->newTestedInstance($conf, $row, $value);
            })
                ->isInstanceOf('Exception')
                ->hasMessage('Callback [trimZ] does not exist')
        ;
    }

    public function testConstruct3()
    {
        $conf = new Conf();
        $conf->name = 'trim';
        $conf->cell = 'str';
        $value = ' aa aa ';
        $row = null;
        $this
            ->object($clb = $this->newTestedInstance($conf, $row, $value))
        ;

        return $clb;
    }

    public function testConstruct4()
    {
        $conf = new Conf();
        $conf->name = ['Datetime', 'createFromFormatZ'];
        $value = ' aa aa ';
        $row = null;
        $this
            ->exception(function()use($conf, $row, $value){
                $this->newTestedInstance($conf, $row, $value);
            })
                ->isInstanceOf('Exception')
                ->hasMessage('Callback [(array) Datetime::createFromFormatZ] does not exist')
        ;
    }

    public function testConstruct5()
    {
        $conf = new Conf();
        $conf->name = ['Datetime', 'createFromFormat'];
        $value = ' aa aa ';
        $row = null;
        $this
            ->exception(function()use($conf, $row, $value){
                $this->newTestedInstance($conf, $row, $value);
            })
                ->isInstanceOf('Exception')
                ->hasMessage('Missing argument [format] for callback [(array) Datetime::createFromFormat]')
        ;
    }

    public function testConstruct6()
    {
        $conf = new Conf();
        $conf->name = ['Datetime', 'createFromFormat'];
        $conf->arguments = new Conf();
        $conf->arguments->format = 'Y-m-d';
        $conf->cell = 'time';

        $value = '2015-10-10';
        $row = null;
        $this
            ->object($clb = $this->newTestedInstance($conf, $row, $value))
        ;

        return $clb;
    }

    public function testConstruct7()
    {
        $conf = new Conf();
        $conf->name = 'array_search';
        $conf->cell = 'needle';
        $conf->row = 'haystack';
        $conf->arguments = new Conf();
        $conf->arguments->strict = true;

        $value = 111;
        $row = [
            'a' => '111',
            'b' => 111,
            'c' => 'cent onze',
            'd' => 'one hundred eleven',
            'e' => 11.1,
        ];
        $this
            ->object($clb = $this->newTestedInstance($conf, $row, $value))
        ;

        return $clb;
    }

    public function testRender3()
    {
        $clb = $this->testConstruct3();

        $this
            ->string($clb->render())
            ->isEqualTo('aa aa')
        ;
    }

    public function testRender6()
    {
        $clb = $this->testConstruct6();

        $this
            ->string($clb->render()->format('d/m/Y'))
            ->isEqualTo('10/10/2015')
        ;
    }

    public function testRender7()
    {
        $clb = $this->testConstruct7();

        $this
            ->string($clb->render())
            ->isEqualTo('b')
        ;
    }
}
