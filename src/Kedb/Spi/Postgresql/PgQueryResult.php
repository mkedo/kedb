<?php
namespace Kedb\Spi\Postgresql;

use Kedb\Spi\SpiQueryResult;

class PgQueryResult implements SpiQueryResult
{
    /**
     * @var PgConnection
     */
    private $connection;

    /**
     * @var resource
     */
    private $result;

    /**
     * @var PgRows
     */
    private $rowsIterator;

    /**
     * @param $connection
     * @param resource $result
     */
    public function __construct($connection, $result)
    {
        $this->connection = $connection;
        $this->result = $result;
        $this->rowsIterator = new PgRows($this->result);
    }

    /**
     * @inheritDoc
     */
    public function rows()
    {
        return $this->rowsIterator;
    }

    /**
     * @inheritDoc
     */
    public function ar()
    {
        return pg_affected_rows($this->result);
    }

    /**
     * @inheritDoc
     */
    public function getLastInsertId()
    {
        foreach ($this->connection->plainQuery("SELECT lastval()")->assoc() as $row) {
            return $row[0];
        }
        return null;
    }
}
