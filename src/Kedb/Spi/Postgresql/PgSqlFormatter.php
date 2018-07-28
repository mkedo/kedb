<?php
namespace Kedb\Spi\Postgresql;


use Kedb\KedbException;
use Kedb\SqlFormatter;

class PgSqlFormatter implements SqlFormatter
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
    public function quoteLiteral($value)
    {
        if (is_null($value)) {
            $result = "NULL";
        } else if (is_int($value) || is_float($value)) {
            $result = (string)$value;
        } else if (is_bool($value)) {
            $result = $value ? 'true' : 'false';
        } else if (is_string($value)) {
            $result = "'" . pg_escape_string($this->connection, $value) . "'";
        } else {
            throw new KedbException("Unsupported literal type: " . gettype($value));
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function quoteIdent($ident)
    {
        if (is_null($ident)) {
            return "NULL";
        }
        $quoteIdent = function ($value) {
            return '"' . str_replace('"', '""', $value) . '"';
        };

        if (is_array($ident)) {
            return implode(".", array_map($quoteIdent, $ident));
        } else {
            return $quoteIdent($ident);
        }
    }
}
