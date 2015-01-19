README
======

## TRIEUR
Trieur is a php library to sort, filter data with differents
* data source (database, csv file...) managed by the <a href="https://github.com/Solire/Trieur/tree/master/Source">Source classes</a>
* query format (array like $_POST...) managed by the <a href="https://github.com/Solire/Trieur/tree/master/Driver">Driver classes</a>
* output format (array, csv formated string...) managed by the <a href="https://github.com/Solire/Trieur/tree/master/Driver">Driver classes</a>

The main classes is a Dependency Injection Container (it extends the famous <a href="https://github.com/silexphp/Pimple">Pimple</a>).
It instanciates a driver class, a source class and a columns configuration class.
Each source and driver class each extend an abstract containing basic methods to communicate through the main class.

It was originally build to print data in ower backend solution to display data, and to export them.
We use dataTables jquery pluggin. Therefore one of the driver available is made for this javascript pluggin.

## USAGE
```php
use Solire\Trieur\Trieur;
use Solire\Conf\Conf;
use Doctrine\DBAL\DriverManager;

// Defining the trieur configuration
$trieurConf = new Conf;
$trieurConf
    ->set('csv', 'driver', 'name')
    ...
    ->set('doctrine', 'source', 'name')
    ...
;

// Defining a source, here we use a doctrine connection
$parameters = [
    'driver' => 'pdo_mysql',
    ...
];
$doctrineConnection = DriverManager::getConnection($parameters);

// Then here goes the magic
$trieur = new Trieur($conf, $doctrineConnection);
$trieur->setRequest($_POST);

$response = $trieur->getResponse();

header('Content-type: application/json');
echo json_encode($response);
```
