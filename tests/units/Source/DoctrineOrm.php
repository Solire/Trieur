<?php

namespace Solire\Trieur\test\units\Source;

use atoum;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Setup;
use Solire\Conf\Loader;
use Solire\Trieur\Columns;
use Solire\Trieur\tests\data\Entity\Profil;

class DoctrineOrm extends atoum
{
    /**
     * Manager d'entité.
     *
     * @var EntityManager
     */
    public $connection = null;

    /**
     * @return EntityManager
     */
    private function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->mockGenerator->shuntParentClassCalls();

        $this->mockGenerator->orphanize('__construct');
        $pdo = new \mock\PDO();

        $this->mockGenerator->orphanize('__construct');
        $db = new \mock\Doctrine\DBAL\Connection();
        $db->getMockController()->connect = function () {
        };
        $db->getMockController()->getEventManager = function () {
            return new EventManager();
        };
        $db->getMockController()->getDatabasePlatform = function () {
            return new MySqlPlatform();
        };
        $this->mockGenerator->unshuntParentClassCalls();

        $config = Setup::createYAMLMetadataConfiguration([
            TEST_DATA_DIR . '/doctrine-orm',
        ], true);

        $this->connection = EntityManager::create($db, $config);

        return $this->connection;
    }

    public function testConstruct01()
    {
        $connection = $this->getConnection();

        $conf = Loader::load([
            'select' => [
                'c.id',
                'c.nom',
            ],
            'from' => [
                [
                    'name' => Profil::class,
                    'alias' => 'c',
                ],
            ],
            'group' => 'c.id',
        ]);

        $columns = new Columns(Loader::load([
            'id' => [
                'source' => 'c.id',
            ],
            'nom' => [
                'source' => 'c.nom',
            ],
        ]));

        /* @var $param Parameter */

        $this
            ->if($c = $this->newTestedInstance($conf, $columns, $connection))
                ->object($c)

                ->object($qB = $c->getQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.id, c.nom FROM ' . Profil::class . ' c')

                ->string($qB->getQuery()->getSQL())
                    ->match('#^SELECT (\w+)\.id AS (\w+), \1\.nom AS (\w+) FROM profil \1$#')

                ->object($qB = $c->getDataQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.id, c.nom FROM ' . Profil::class . ' c GROUP BY c.id')

                ->string($qB->getQuery()->getSQL())
                    ->match('#^SELECT (\w+)\.id AS (\w+), \1\.nom AS (\w+) FROM profil \1 GROUP BY \1.id$#')

                ->object($qB = $c->getCountQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT COUNT(DISTINCT c.id) FROM ' . Profil::class . ' c')

                ->string($qB->getQuery()->getSQL())
                    ->match('#^SELECT COUNT\(DISTINCT (\w+)\.id\) AS (\w+) FROM profil \1$#')

                ->object($qB = $c->getFilteredCountQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT COUNT(DISTINCT c.id) FROM ' . Profil::class . ' c')

                ->string($qB->getQuery()->getSQL())
                    ->match('#^SELECT COUNT\(DISTINCT (\w+)\.id\) AS (\w+) FROM profil \1$#')

                ->if($term = 'audi')
                ->and($c->addFilter([
                    'c.nom',
                    $term,
                    'Contain',
                ]))

                ->object($qB = $c->getDataQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.id, c.nom FROM ' . Profil::class . ' c WHERE c.nom LIKE :word_1 GROUP BY c.id')

                ->string($qB->getQuery()->getSQL())
                    ->match('#^SELECT (\w+)\.id AS (\w+), \1\.nom AS (\w+) FROM profil \1 WHERE \1\.nom LIKE \? GROUP BY \1\.id$#')

                ->object($param = $qB->getParameter('word_1'))
                    ->isInstanceOf(Parameter::class)

                ->string($param->getValue())
                    ->isEqualTo('%' . $term . '%')

                ->if($c->setLength(1))
                ->and($c->setOffset(1))
                ->object($qB = $c->getDataQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.id, c.nom FROM ' . Profil::class . ' c WHERE c.nom LIKE :word_1 GROUP BY c.id')

                ->string($qB->getQuery()->getSQL())
                    ->match('#^SELECT (\w+)\.id AS (\w+), \1\.nom AS (\w+) FROM profil \1 WHERE \1\.nom LIKE \? GROUP BY \1\.id LIMIT 1 OFFSET 1$#')

                ->if($c->addOrder('id'))
                ->object($qB = $c->getDataQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.id, c.nom FROM ' . Profil::class . ' c WHERE c.nom LIKE :word_1 GROUP BY c.id ORDER BY c.id ASC')

                ->string($qB->getQuery()->getSQL())
                    ->match('#^SELECT (\w+)\.id AS (\w+), \1\.nom AS (\w+) FROM profil \1 WHERE \1\.nom LIKE \? GROUP BY \1\.id ORDER BY \1\.id ASC LIMIT 1 OFFSET 1$#')
        ;
    }
}
