<?php
namespace Kedb\Spi\Fake;

class FakeSqlFormatter implements \Kedb\SqlFormatter
{

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
            $result = "'" . str_replace("'", "''", $value) . "'";
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
