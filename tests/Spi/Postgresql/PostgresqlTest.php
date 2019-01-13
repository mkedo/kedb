<?php
namespace Kedb\Tests\Spi\Postgresql;

use Kedb\Connection;
use Kedb\Query;
use Kedb\Spi\Postgresql\PgConnection;
use Kedb\Transaction;
use Kedb\TransactionException;

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

        $dbConfigFile = __DIR__ . '/../../db.params.php';
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

    public function testTransaction()
    {
        $txn = $this->pg->transaction();
        $conn = $this->pg;

        $this->assertTrue(! $txn->isInTransaction());
        $txn->begin();
        $this->assertTrue($txn->isInTransaction());
        $txn->commit();
        $this->assertTrue(! $txn->isInTransaction());

        $tableName = "_pg_driver_test_";
        $newTableQuery = new Query("CREATE TEMP TABLE ?t (?t integer)", [$tableName, "id"]);
        $insertQuery = new Query("INSERT INTO ?t VALUES (?)", [$tableName, 123]);
        $countQuery = new Query("SELECT COUNT(*) FROM ?t", [$tableName]);

        $newTableQuery->exec($conn);


        $this->assertEquals(0, $countQuery->exec($conn)->el());

        // begin 1
        $txn->begin();
        $insertQuery->exec($conn);
        $this->assertTrue($txn->isInTransaction());
        $this->assertEquals(1, $countQuery->exec($conn)->el());

            // begin 2
            $txn->begin();
            $this->assertTrue($txn->isInTransaction());
            $insertQuery->exec($conn);
            $this->assertEquals(2, $countQuery->exec($conn)->el());
                // begin 3
                $txn->begin();
                $this->assertTrue($txn->isInTransaction());
                $insertQuery->exec($conn);
                $this->assertEquals(3, $countQuery->exec($conn)->el());

                // end 3
                $txn->commit();
                $this->assertTrue($txn->isInTransaction());
                $this->assertEquals(3, $countQuery->exec($conn)->el());

            // end 2
            $txn->rollback();
            $this->assertTrue($txn->isInTransaction());
            $this->assertEquals(1, $countQuery->exec($conn)->el());

        // end 1
        $txn->rollback();
        $this->assertTrue(! $txn->isInTransaction());
        $this->assertEquals(0, $countQuery->exec($conn)->el());

        try {
            $txn->commit();
            $this->fail("Should not execute since there is no transaction in progress");
        } catch (TransactionException $e) {}

        try {
            $txn->rollback();
            $this->fail("Should not execute since there is no transaction in progress");
        } catch (TransactionException $e) {}

    }

    public function testTxnLevel()
    {
        $txn = $this->pg->transaction();

        $txn->begin(Transaction::REPEATABLE_READ);
        try {
            $txn->begin(Transaction::SERIALIZABLE);
            $this->fail("Should not raise isolation level");
        } catch (TransactionException $e) {}

        try {
            $txn->begin(12345678);
            $this->fail("Should not start with unknown isolation level");
        } catch (TransactionException $e) {}

        $txn->rollback();
    }
}
