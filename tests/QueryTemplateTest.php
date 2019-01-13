<?php
namespace Kedb\Tests;

use Kedb\KedbException;
use Kedb\QueryTemplate;
use Kedb\Spi\Common\CmSqlFormatter;
use Kedb\Spi\Fake\FakeSqlFormatter;

class QueryTemplateTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider phProvider
     * @param string $tpl
     * @param array $params
     * @param string $expected
     * @throws KedbException
     */
    public function testFetch($tpl, $params, $expected)
    {
        $formatter = new CmSqlFormatter(new FakeSqlFormatter());
        $template = new QueryTemplate($tpl);

        $sql = $template->fetch($formatter, $params);

        $this->assertSame($expected, $sql);
    }

    public function phProvider()
    {
        return [
            't' => ['?t', ['name'], '"name"'],
            'c' => [
                '?c',
                [[
                    ['schema', 'table', 'column'],
                    'col'
                ]],
                '"schema"."table"."column","col"'
            ],
            'b' => ['?b', ["\x01\x02\x03"], '\'\x010203\'::bytea'],
            'b_null' => ['?b', [null], 'NULL'],
            'q' => ['?q', ['raw'], 'raw']
        ];
    }

    public function testThrowUnknownPlaceholderType()
    {
        $formatter = new CmSqlFormatter(new FakeSqlFormatter());
        $template = new QueryTemplate('?z');

        $this->expectException(KedbException::class);

        $template->fetch($formatter, ['abc']);
    }
}
