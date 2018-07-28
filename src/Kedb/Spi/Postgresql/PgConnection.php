<?php

namespace Kedb\Spi\Postgresql;

use Kedb\Connection;
use Kedb\KedbException;
use Kedb\SqlFormatter;

class PgConnection implements Connection
{
    /**
     * @var string
     */
    private $dsn;

    /**
     * @var resource
     */
    private $connection;

    /**
     * @var SqlFormatter
     */
    private $formatter;

    /**
     * @param string $dsn
     * @throws KedbException
     */
    public function __construct($dsn)
    {
        $this->dsn = $dsn;
        if (!extension_loaded('pgsql')) {
            throw new KedbException("pgsql extension required");
        }
    }


    public function connect()
    {
        $connection = @pg_connect($this->dsn, PGSQL_CONNECT_FORCE_NEW);
        if (!$connection) {
            $errorData = error_get_last();
            throw new KedbException($errorData);
        }
        pg_set_error_verbosity($connection, PGSQL_ERRORS_VERBOSE);
        $this->connection = $connection;
        $this->formatter = new PgSqlFormatter($connection);
    }

    /**
     * @inheritDoc
     */
    public function plainQuery($sql)
    {
        $this->checkConnection();
        $result = @pg_query($this->connection, $sql);
        if (!$result) {
            throw new KedbException(pg_last_error($this->connection));
        }
        return new PgQueryResult($result);
    }

    public function transaction()
    {
        // TODO: Implement transaction() method.
        throw new \Exception("Not implemented");
        $this->checkConnection();
    }

    public function close()
    {
        if ($this->connection) {
            @pg_close($this->connection);
            $this->connection = null;
            $this->formatter = null;
        }
    }

    /**
     * @inheritDoc
     */
    public function formatter()
    {
        $this->checkConnection();
        return $this->formatter;
    }

    private function checkConnection()
    {
        if (!$this->connection) {
            throw new KedbException("Not connected");
        }
    }
}
