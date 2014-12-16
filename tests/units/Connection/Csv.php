<?php
namespace Solire\Trieur\test\units\Connection;

use \atoum as Atoum;
use Solire\Trieur\Connection\Csv as TestClass;

use Solire\Conf\Conf;

class Csv extends Atoum
{
    protected $fileName = 'clients.csv';

    public function connection()
    {
        return TEST_TMP_DIR . DIRECTORY_SEPARATOR . $this->fileName;
    }

    public function setUp()
    {
        $handle = fopen($this->connection(), 'w');
        $data = [
            ['1', 'a', '3', 'thomas'],
            ['2', 'z', '2', 'thomas'],
            ['3', 'z', '2', 'jérôme'],
            ['4', 't', '5', 'julie'],
            ['5', 't', '5', 'abel'],
            ['6', 'c', '5', 'julie'],
        ];
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
    }

    public function tearDown()
    {
        unlink($this->connection());
    }

    public function testConstruct01()
    {
        $conf = arrayToConf([

        ]);

        $columns = arrayToConf([
            'lettre' => [],
            'numero' => [],
            'nom' => [],
        ]);

        $this
            ->if($c = new TestClass(
                    TEST_TMP_DIR . DIRECTORY_SEPARATOR . $this->fileName,
                    $conf,
                    $columns
                )
            )
            ->integer($c->getCount())
            ->isEqualTo(6)

            ->array($c->getData())
            ->isEqualTo([
                ['1', 'a', '3', 'thomas'],
                ['2', 'z', '2', 'thomas'],
                ['3', 'z', '2', 'jérôme'],
                ['4', 't', '5', 'julie'],
                ['5', 't', '5', 'abel'],
                ['6', 'c', '5', 'julie'],
            ])

            ->and($c->addOrder('1', 'asc'))
            ->array($c->getData())
            ->isEqualTo([
                ['1', 'a', '3', 'thomas'],
                ['6', 'c', '5', 'julie'],
                ['4', 't', '5', 'julie'],
                ['5', 't', '5', 'abel'],
                ['2', 'z', '2', 'thomas'],
                ['3', 'z', '2', 'jérôme'],
            ])

            ->and($c->addOrder('3', 'asc'))
            ->array($c->getData())
            ->isEqualTo([
                ['1', 'a', '3', 'thomas'],
                ['6', 'c', '5', 'julie'],
                ['5', 't', '5', 'abel'],
                ['4', 't', '5', 'julie'],
                ['3', 'z', '2', 'jérôme'],
                ['2', 'z', '2', 'thomas'],
            ])

            ->and($c->setOffset(2))
            ->and($c->setLength(3))
            ->array($c->getData())
            ->isEqualTo([
                ['5', 't', '5', 'abel'],
                ['4', 't', '5', 'julie'],
                ['3', 'z', '2', 'jérôme'],
            ])

            ->and($c->setOffset(0))
            ->and(
                $c->addSearch([[
                    [3],
                    ['a'],
                ]])
            )
            ->array($c->getData())
            ->isEqualTo([
                ['1', 'a', '3', 'thomas'],
                ['5', 't', '5', 'abel'],
                ['2', 'z', '2', 'thomas'],
            ])

            ->and(
                $c->addSearch([[
                    [3],
                    ['th'],
                ]])
            )
            ->array($c->getData())
            ->isEqualTo([
                ['1', 'a', '3', 'thomas'],
                ['2', 'z', '2', 'thomas'],
            ])
        ;
    }
}
