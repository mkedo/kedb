<?php
namespace Kedb\Tests\Spi\Fake;

use Kedb\Spi\Common\CmSqlFormatter;
use Kedb\Spi\Fake\FakeSqlFormatter;

class FakeSqlFormatterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider identProvider
     * @param string|array $ident
     * @param string $expected
     */
    public function testQuoteIdent($ident, $expected)
    {
        $formatter = new CmSqlFormatter(new FakeSqlFormatter());
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
        $formatter = new CmSqlFormatter(new FakeSqlFormatter());
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

    /**
     * @dataProvider binaryProvider
     * @param string $binary
     * @param string $expected
     */
    public function testBinary($binary, $expected)
    {
        $formatter = new CmSqlFormatter(new FakeSqlFormatter());

        $this->assertEquals($expected, $formatter->quoteBinary($binary));
    }

    public function binaryProvider()
    {
        return [
            ["\x01\x02", sprintf("'%s'::bytea", '\\x0102')],
            ["", "''::bytea"],
        ];
    }

    /**
     * @dataProvider binaryProvider
     * @param string $binary
     * @param string $expected
     */
    public function testResource($binary, $expected)
    {
        $formatter = new CmSqlFormatter(new FakeSqlFormatter());

        $fp = fopen("php://memory", "rwb");
        $this->assertTrue(is_resource($fp));
        fwrite($fp, $binary);
        fseek($fp, 0);

        try {
            $this->assertEquals($expected, $formatter->quoteLiteral($fp));
        } finally {
            fclose($fp);
        }
    }
}
