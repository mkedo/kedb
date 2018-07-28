<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 0027 27.07.2018
 * Time: 1:11
 */

namespace Kedb\Spi\Fake;


class FakeSqlFormatterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider identProvider
     * @param string|array $ident
     * @param string $expected
     */
    public function testQuoteIdent($ident, $expected)
    {
        $formatter = new FakeSqlFormatter();
        $this->assertEquals($expected, $formatter->quoteIdent($ident));
    }

    public function identProvider()
    {
        return [
            ['', '""'],
            ['abc', '"abc"'],
            ['a"b"c', '"a""b""c"'],
            [['a', 'b', 'c'], '"a"."b"."c"'],
            [null, 'NULL']
        ];
    }

    /**
     * @dataProvider literalProvider
     */
    public function testQuoteLiteral($literal, $expected)
    {
        $formatter = new FakeSqlFormatter();
        $this->assertEquals($expected, $formatter->quoteLiteral($literal));
    }

    public function literalProvider()
    {
        return [
            ['', "''"],
            ["a", "'a'"],
            ["a'b'c", "'a''b''c'"],
            [null, 'NULL'],
            [123, '123'],
            [1.23, '1.23'],
            [true, 'true'],
            [false, 'false'],
        ];
    }
}
