<?php
namespace Solire\Trieur\test\units\Source;

use atoum as Atoum;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Setup;
use Solire\Conf\Conf;
use Solire\Trieur\Columns;
use Solire\Trieur\Source\DoctrineOrm as TestClass;
use Solire\Trieur\tests\data\Entity\Profil;

class DoctrineOrm extends Atoum
{
    /**
     * Manager d'entité
     *
     * @var EntityManager
     */
    public $connection = null;

    /**
     *
     * @return EntityManager
     */
    private function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->mockGenerator->shuntParentClassCalls();

        $this->mockGenerator->orphanize('__construct');
        $pdo = new \mock\PDO;

        $this->mockGenerator->orphanize('__construct');
        $db = new \mock\Doctrine\DBAL\Connection;
        $db->getMockController()->connect = function() {};
        $db->getMockController()->getEventManager = function() {
            return new EventManager();;
        };
        $db->getMockController()->getDatabasePlatform = function() {
            return new MySqlPlatform;
        };
        $this->mockGenerator->unshuntParentClassCalls();

        $config = Setup::createYAMLMetadataConfiguration([
            TEST_DATA_DIR . '/doctrine-orm'
        ], true);

        $this->connection = EntityManager::create($db, $config);

        return $this->connection;
    }

    public function testConstruct01()
    {
        $connection = $this->getConnection();

        $conf = \Solire\Conf\Loader::load([
            'select' => [
                'c.nom',
            ],
            'from' => [
                [
                    'name' => Profil::class,
                    'alias' => 'c',
                ],
            ],
        ]);

        $columns = new Columns(new Conf);

        /* @var $c TestClass */

        $this
            ->if($c = new TestClass($conf, $columns, $connection))
                ->object($c)


                ->object($qB = $c->getQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.nom FROM ' . Profil::class . ' c')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
                    ->match('#^SELECT (\w+)\.nom AS (\w+) FROM profil \1$#')


                ->object($qB = $c->getDataQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.nom FROM ' . Profil::class . ' c')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
                    ->match('#^SELECT (\w+)\.nom AS (\w+) FROM profil \1$#')


                ->object($qB = $c->getCountQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT COUNT(DISTINCT c.nom) FROM ' . Profil::class . ' c')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
                    ->match('#^SELECT COUNT\(DISTINCT (\w+)\.nom\) AS (\w+) FROM profil \1$#')


                ->object($qB = $c->getFilteredCountQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT COUNT(DISTINCT c.nom) FROM ' . Profil::class . ' c')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
                    ->match('#^SELECT COUNT\(DISTINCT (\w+)\.nom\) AS (\w+) FROM profil \1$#')


                ->if ($c->addFilter([
                    'c.nom',
                    'audi',
                    'Contain'
                ]))

                ->object($qB = $c->getDataQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.nom FROM ' . Profil::class . ' c WHERE c.nom LIKE :word_1')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
                    ->match('#^SELECT (\w+)\.nom AS (\w+) FROM profil \1 WHERE \1\.nom LIKE \?$#')
        ;
    }

    public function testConstruct02()
    {
        $connection = $this->getConnection();

        $conf = \Solire\Conf\Loader::load([
            'select' => [
                'c.nom',
            ],
            'from' => [
                [
                    'name' => Profil::class,
                    'alias' => 'c',
                ],
            ],
            'where' => [
                'c.nom LIKE :foo',
            ],
            'parameters' => [
                'foo' => 'a',
            ]
        ]);

        $columns = new Columns(new Conf);

        /* @var $c TestClass */

        $this
            ->if($c = new TestClass($conf, $columns, $connection))
                ->object($c)


                ->object($qB = $c->getQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.nom FROM ' . Profil::class . ' c WHERE c.nom LIKE :foo')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
                    ->match('#^SELECT (\w+)\.nom AS (\w+) FROM profil \1 WHERE \1\.nom LIKE \?$#')


                ->object($qB = $c->getDataQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.nom FROM ' . Profil::class . ' c WHERE c.nom LIKE :foo')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
                    ->match('#^SELECT (\w+)\.nom AS (\w+) FROM profil \1 WHERE \1\.nom LIKE \?$#')


                ->object($qB = $c->getCountQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT COUNT(DISTINCT c.nom) FROM ' . Profil::class . ' c WHERE c.nom LIKE :foo')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
                    ->match('#^SELECT COUNT\(DISTINCT (\w+)\.nom\) AS (\w+) FROM profil \1 WHERE \1\.nom LIKE \?$#')


                ->object($qB = $c->getFilteredCountQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT COUNT(DISTINCT c.nom) FROM ' . Profil::class . ' c WHERE c.nom LIKE :foo')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
                    ->match('#^SELECT COUNT\(DISTINCT (\w+)\.nom\) AS (\w+) FROM profil \1 WHERE \1\.nom LIKE \?$#')


                ->if ($c->addFilter([
                    'c.nom',
                    'audi',
                    'Contain'
                ]))

                ->object($qB = $c->getDataQuery())
                    ->isInstanceOf(QueryBuilder::class)

                ->string($dql = $qB->getDQL())
                    ->isEqualTo('SELECT c.nom FROM ' . Profil::class . ' c WHERE c.nom LIKE :foo AND c.nom LIKE :word_1')

                ->string($this->getConnection()->createQuery($dql)->getSQL())
                    ->match('#^SELECT (\w+)\.nom AS (\w+) FROM profil \1 WHERE \1\.nom LIKE \? AND \1\.nom LIKE \?$#')
        ;
    }
}
