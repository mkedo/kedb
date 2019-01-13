<?php

namespace Kedb\Tests\Template;

use Kedb\Template\Placeholder;
use Kedb\Template\PlaceholderTemplate;

class PlaceholderTemplateTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider provider
     */
    public function testFetch($tpl, $params, $expected, $expectedPhs)
    {
        $template = new PlaceholderTemplate($tpl);

        $sql = $template->fetch($params);
        $phs = $template->getPlaceholders();

        $this->assertEquals($expected, $sql);
        $this->assertEquals($expectedPhs, $phs);
    }

    public function provider()
    {
        $identType = 't';
        return [
            ['', [], '', []],
            ['?', [1], '1', [new Placeholder(0)]],
            ['?t', [1], '1', [new Placeholder(0, $identType)]],
            ['?:named', ['named' => 1], '1', [new Placeholder('named')]],
            ['?t:named', ['named' => 1], '1', [new Placeholder('named', $identType)]],
            ['\?', [], '?', []],
            ['\?t', [], '?t', []],
            ['\?t:named', [], '?t:named', []], // 7
            ['?t:name1:name2', ['name1'=> 'a'], 'a:name2', [new Placeholder('name1', $identType)]],
            [':name1', [], ':name1', []],

            [
                'SELECT "no" FROM "placeholders"',
                [],
                'SELECT "no" FROM "placeholders"',
                []
            ],

            [
                'SELECT ?',
                ['a'],
                'SELECT a',
                [
                    new Placeholder(0)
                ],
            ],

            [
                'SELECT ?:col FROM ?:table WHERE id = ?:id LIMIT 1',
                [
                    'col' => 'column',
                    'table' => 'test_tale',
                    'id' => '123'
                ],
                'SELECT column FROM test_tale WHERE id = 123 LIMIT 1',
                [
                    new Placeholder('col'),
                    new Placeholder('table'),
                    new Placeholder('id'),
                ]
            ],

            [
                'SELECT ?t:col FROM ?t:table WHERE id = ?:id LIMIT 1',
                [
                    'col' => 'column',
                    'table' => 'test_tale',
                    'id' => '123'
                ],
                'SELECT column FROM test_tale WHERE id = 123 LIMIT 1',
                [
                    new Placeholder('col', $identType),
                    new Placeholder('table', $identType),
                    new Placeholder('id'),
                ]
            ],

            [
                'SELECT ??',
                ['a', 'b'],
                'SELECT ab',
                [new Placeholder(0), new Placeholder(1)]
            ],
            [
                'SELECT "\?"',
                [],
                'SELECT "?"',
                []
            ],

            [
                '?t:name-dash?t:another_name',
                ['name-dash' => 'a', 'another_name' => 'b' ],
                'ab',
                [new Placeholder('name-dash', $identType), new Placeholder('another_name', $identType)]
            ],

            [
                'SELECT ?t:id FROM ?t:table WHERE id = ?:id',
                ['id' => 1, 'table' => 't'],
                'SELECT 1 FROM t WHERE id = 1',
                [new Placeholder('id', $identType), new Placeholder('table', $identType), new Placeholder('id')]
            ],
            [
                '?:p1 ?:p2',
                ['extra1' => 'a', 'p1' => 'b', 'p2' => 'c', 'extra2' => 'c', 'params' => 'd'],
                'b c',
                [
                    new Placeholder('p1'),
                    new Placeholder('p2')
                ]
            ],
            // custom placeholder type
            [
                '?custom:name',
                ['name' => 'a'],
                'a',
                [new Placeholder('name', 'custom')]
            ]
        ];
    }
}
