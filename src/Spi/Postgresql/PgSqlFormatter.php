<?php
namespace Kedb\Spi\Postgresql;

use Kedb\Spi\SpiSqlFormatter;

class PgSqlFormatter implements SpiSqlFormatter
{
    /**
     * @var resource
     */
    private $connection;

    /**
     * @param resource $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function formatIdent($ident)
    {
        $connection = $this->connection;

        $quoteIdent = function ($value) use (&$connection) {
            return pg_escape_identifier($connection, $value);
        };

        if (is_array($ident)) {
            return implode(".", array_map($quoteIdent, $ident));
        } else {
            return $quoteIdent($ident);
        }
    }

    /**
     * @inheritDoc
     */
    public function formatNull()
    {
        return "NULL";
    }

    /**
     * @inheritDoc
     */
    public function formatInt($value)
    {
        return (string)$value;
    }

    /**
     * @inheritDoc
     */
    public function formatFloat($value)
    {
        return (string)$value;
    }

    /**
     * @inheritDoc
     */
    public function formatBool($value)
    {
        return $value ? 'true' : 'false';
    }

    /**
     * @inheritDoc
     */
    public function formatString($value)
    {
        return "'" . pg_escape_string($this->connection, $value) . "'";
    }

    /**
     * @inheritDoc
     */
    public function formatDefault()
    {
        return "DEFAULT";
    }

    /**
     * @inheritDoc
     */
    public function formatBinary($value)
    {
        return "'" . pg_escape_bytea($this->connection, $value) . "'::bytea";
    }
}
