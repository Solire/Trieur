<?php

require __DIR__ . '/../vendor/autoload.php';

function autoload($name)
{
    if ($name[0] == '\\') {
        $name =  substr($name, 1);
    }
    $name = preg_replace('`^Solire\\\Trieur\\\`', '', $name);

    $path = __DIR__ . '/../' . str_replace('\\', '/', $name) . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
}

spl_autoload_register('autoload');
