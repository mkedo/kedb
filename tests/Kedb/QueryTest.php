<?php
namespace Kedb;


use Kedb\Spi\Fake\FakeSqlFormatter;

class QueryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider formatProvider
     * @param string $sql
     * @param array $params
     * @param string $expected
     */
    public function testFormat($sql, $params, $expected)
    {
        $formatter = new FakeSqlFormatter();
        $query = new Query($sql, $params);

        $rawSql = $query->format($formatter);

        $this->assertSame($expected, $rawSql);
    }

    public function formatProvider()
    {
        return [
            ['', [], ''],
            ["SELECT ?", [1], "SELECT 1"],
            ["SELECT ?, ?t", [1,1], "SELECT 1, \"1\""],
            [
                "SELECT ?t:col FROM ?t:table WHERE id=?:id",
                [
                    'col' => 'col"name',
                    'table' => 'tab"ble',
                    'id' => 's\'tr'
                ],
                "SELECT \"col\"\"name\" FROM \"tab\"\"ble\" WHERE id='s''tr'"
            ],
            [
                "SELECT ?:null, ?:str, ?:int, ?:float",
                [
                    'null' => null,
                    'str' => 'str',
                    'int' => 123,
                    'float' => 1.23
                ],
                "SELECT NULL, 'str', 123, 1.23"
            ]
        ];
    }

    public function testExec()
    {

    }
}
