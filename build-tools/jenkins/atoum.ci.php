<?php
// build-tools/jenkins/atoum.ci.php

require_once __DIR__ . '/../../vendor/atoum/atoum/classes/autoloader.php';

/*
 * CLI report.
 */
$stdOutWriter = new \mageekguy\atoum\writers\std\out();
$cli = new \mageekguy\atoum\reports\realtime\cli();
$cli->addWriter($stdOutWriter);

$basedir = __DIR__ . '/../../';

/*
 * Xunit report
 */
$xunit = new \mageekguy\atoum\reports\asynchronous\xunit();
/*
 * Xunit writer
 */
$writer = new \mageekguy\atoum\writers\file($basedir . '/build/logs/junit.xml');
$xunit->addWriter($writer);

/*
 * Clover coverage
 */
$cloverWriter = new \mageekguy\atoum\writers\file($basedir . '/build/logs/clover.xml');
$cloverReport = new atoum\reports\asynchronous\clover();
$cloverReport->addWriter($cloverWriter);


/*
 * Html coverage
 */
$html = new \mageekguy\atoum\report\fields\runner\coverage\html('Solire\Trieur', $basedir . '/build/coverage');
$cli->addField($html);

$runner->addReport($xunit);
$runner->addReport($cli);
$runner->addReport($cloverReport);
