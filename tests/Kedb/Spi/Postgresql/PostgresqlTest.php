<?php
namespace Kedb\Spi\Postgresql;

use Kedb\Connection;
use Kedb\Query;

class PostgresqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $pg;

    protected function setUp()
    {
        parent::setUp();
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped("The pgsql extension is not available.");
        }

        $dbConfigFile = __DIR__ . '/../../../db.params.php';
        if (!is_readable($dbConfigFile)) {
            $this->markTestSkipped("Database configuration file not found tests/db.params.php or is not readable");
        }
        $dbConfig = require($dbConfigFile);

        if (!isset($dbConfig['pgsql'])) {
            $this->markTestSkipped("No configuration for pgsql is present in the database config");
        }

        $this->pg = new PgConnection($dbConfig['pgsql']);
        $this->pg->connect();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->pg->close();
    }

    public function testFormatter()
    {
        $bin = "\x01\x02\x03";
        $fp = fopen("php://memory", "rwb");
        fwrite($fp, $bin);
        fseek($fp, 0);

        $data = [
            'n' => null,
            'i' => 123,
            'f' => 1.23,
            'bt' => true,
            'bf' => false,
            'str' => 'string',
            'bin' => $fp,
            'ident' => "ident"
        ];

        try {
            $query = new Query("SELECT ?:n n, ?:i i, ?:f f, ?:bt bt, ?:bf bf, ?:str str, ?:bin bin, 1 ?t:ident", $data);
            $actualSql = $query->format($this->pg->formatter());
            fseek($fp, 0);
            $row = $query->exec($this->pg)->row();

            $this->assertTrue(!empty($row));
            $exceptedSql = 'SELECT NULL n, 123 i, 1.23 f, true bt, false bf, \'string\' str, \'\x010203\'::bytea bin, 1 "ident"';
            $this->assertSame($exceptedSql, $actualSql);
            $this->assertSame($row['n'], $data['n']);
            $this->assertTrue($row['i'] == $data['i']);
            $this->assertTrue(abs($row['f'] - $data['f']) < 0.00001);
            $this->assertSame($row['bt'], $data['bt']);
            $this->assertSame($row['bf'], $data['bf']);
            $this->assertSame($row['str'], $data['str']);
            $this->assertSame($row['bin'], $bin);

        } finally {
            fclose($fp);
        }
    }

    public function testFetchers()
    {
        $rows = [
            ["id" => 1, "name" => 'one'],
            ["id" => 2, "name" => 'two'],
            ["id" => 3, "name" => 'three'],
        ];

        $idRows = [];
        foreach ($rows as $row) {
            $idRows []= $row["id"];
        }

        $nameMap = [];
        foreach ($rows as $row) {
            $nameMap[$row["name"]]= $row;
        }


        $query = new Query("WITH result AS ("
            ."SELECT * FROM (VALUES (1, 'one'), (2, 'two'), (3, 'three')) AS t (id, name)"
            .") "
            ." SELECT ?t:id, ?t:name FROM result ORDER by id ASC",
        [
            'id' => ["result", "id"],
            'name' => ["result", "name"],
        ]
        );

        $result = $query->exec($this->pg);

        $this->assertEquals(count($rows), $result->getNumRows());

        // assoc
        $this->assertEquals($rows, $result->assoc());
        $this->assertEquals($nameMap, $result->assoc("name"));

        // row
        $this->assertEquals($rows[0], $result->row());

        // col
        $this->assertEquals($idRows, $result->col("id"));
        $this->assertEquals($idRows, $result->col());

        // el
        $this->assertEquals("one", $result->el("name"));
        $this->assertEquals(1, $result->el());
    }
}
