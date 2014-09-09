<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace tests\units\solire\trieur;

use \atoum;

/**
 * Description of Trieur
 *
 * @author thansen
 */
class Trieur extends \atoum
{
    public function testGetDriver()
    {
        $this
            ->if($driver = \solire\trieur\Trieur::getDriver(array(
                '__dataTables' => array(
                    'item' => 'objet',
                    'items' => 'objets',
                ),
                'id' => array(
                    'titre' => 'Identifiant',
                    'sql' => 'id'
                )
            )))
            ->object($driver)
                ->isInstanceOf('\\solire\\trieur\\Driver')
        ;

        $this
            ->if($driver = \solire\trieur\Trieur::getDriver(array(
                '__dataTables' => array(
                    'item' => 'objet',
                    'items' => 'objets',
                ),
                'id' => array(
                    'titre' => 'Identifiant',
                    'sql' => 'id'
                )
            ), 'DataTables'))
            ->object($driver)
                ->isInstanceOf('\\solire\\trieur\\Driver')
        ;
    }
}
