<?php

namespace Solire\Trieur\test\units\Source;

use atoum;
use Solire\Conf\Loader;
use Solire\Trieur\Columns;

class Csv extends atoum
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

        touch(TEST_TMP_DIR . DIRECTORY_SEPARATOR . 'notreadable.csv');
        chmod(TEST_TMP_DIR . DIRECTORY_SEPARATOR . 'notreadable.csv', 0333);
    }

    public function tearDown()
    {
        unlink($this->connection());
        unlink(TEST_TMP_DIR . DIRECTORY_SEPARATOR . 'notreadable.csv');
    }

    public function testConstruct00()
    {
        $conf = Loader::load([]);

        $columns = new Columns(Loader::load([
            'lettre' => [],
            'numero' => [],
            'nom' => [],
        ]));

        $this
            ->exception(function()use($conf, $columns){
                $this->newTestedInstance(
                    $conf,
                    $columns,
                    TEST_TMP_DIR . DIRECTORY_SEPARATOR . 'notfouned.csv'
                );
            })
                ->isInstanceOf('Exception')
                ->hasMessage('No csv file founed : "' . TEST_TMP_DIR . DIRECTORY_SEPARATOR . 'notfouned.csv' . '"')
        ;
    }

    public function testConstruct01()
    {
        $conf = Loader::load([]);

        $columns = new Columns(Loader::load([
            'lettre' => [],
            'numero' => [],
            'nom' => [],
        ]));

        $this
            ->exception(function()use($conf, $columns){
                $this->newTestedInstance(
                    $conf,
                    $columns,
                    TEST_TMP_DIR . DIRECTORY_SEPARATOR . 'notreadable.csv'
                );
            })
                ->isInstanceOf('Exception')
                ->hasMessage('Csv file not readable : "' . realpath(TEST_TMP_DIR) . DIRECTORY_SEPARATOR . 'notreadable.csv' . '"')
        ;
    }

    public function testConstruct02()
    {
        $conf = Loader::load([]);

        $columns = new Columns(Loader::load([
            '0' => [
                'sort' => 1,
            ],
            '1' => [
                'sort' => 1,
            ],
            '2' => [
                'sort' => 1,
            ],
            '3' => [
                'sort' => 1,
            ],
        ]));

        $this
            ->if($c = $this->newTestedInstance(
                    $conf,
                    $columns,
                    TEST_TMP_DIR . DIRECTORY_SEPARATOR . $this->fileName
                )
            )
            ->integer($c->getCount())
            ->isEqualTo(6)
            ->phpArray($c->getData())
            ->isEqualTo([
                ['1', 'a', '3', 'thomas'],
                ['2', 'z', '2', 'thomas'],
                ['3', 'z', '2', 'jérôme'],
                ['4', 't', '5', 'julie'],
                ['5', 't', '5', 'abel'],
                ['6', 'c', '5', 'julie'],
            ])

            ->and($c->setOrders([
                ['1', 'asc']
            ]))
            ->phpArray($c->getData())
            ->isEqualTo([
                ['1', 'a', '3', 'thomas'],
                ['6', 'c', '5', 'julie'],
                ['4', 't', '5', 'julie'],
                ['5', 't', '5', 'abel'],
                ['2', 'z', '2', 'thomas'],
                ['3', 'z', '2', 'jérôme'],
            ])

            ->and($c->addOrder('3', 'asc'))
            ->phpArray($c->getData())
            ->isEqualTo([
                ['1', 'a', '3', 'thomas'],
                ['6', 'c', '5', 'julie'],
                ['5', 't', '5', 'abel'],
                ['4', 't', '5', 'julie'],
                ['3', 'z', '2', 'jérôme'],
                ['2', 'z', '2', 'thomas'],
            ])

            ->and($c->setOrders([
                ['1', 'desc'],
                ['3', 'desc'],
            ]))
            ->phpArray($c->getData())
            ->isEqualTo([
                ['2', 'z', '2', 'thomas'],
                ['3', 'z', '2', 'jérôme'],
                ['4', 't', '5', 'julie'],
                ['5', 't', '5', 'abel'],
                ['6', 'c', '5', 'julie'],
                ['1', 'a', '3', 'thomas'],
            ])

            ->and($c->setOffset(2))
            ->and($c->setLength(3))
            ->phpArray($c->getData())
            ->isEqualTo([
                ['4', 't', '5', 'julie'],
                ['5', 't', '5', 'abel'],
                ['6', 'c', '5', 'julie'],
            ])

            ->and($c->setOffset(0))
            ->and(
                $c->setFilters([
                    [
                        [3],
                        ['a'],
                        'Contain'
                    ],
                ])
            )
            ->phpArray($c->getData())
            ->isEqualTo([
                ['2', 'z', '2', 'thomas'],
                ['5', 't', '5', 'abel'],
                ['1', 'a', '3', 'thomas'],
            ])

            ->and(
                $c->addFilter([
                    [3],
                    'th',
                    'Contain'
                ])
            )
            ->phpArray($c->getData())
            ->isEqualTo([
                ['2', 'z', '2', 'thomas'],
                ['1', 'a', '3', 'thomas'],
            ])

            ->integer($c->getFilteredCount())
            ->isEqualTo(2)
        ;
    }
}
