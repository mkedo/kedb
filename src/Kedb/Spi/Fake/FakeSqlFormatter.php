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
        } else if (is_resource($value)) {
            if (get_resource_type($value) !== 'stream') {
                throw new KedbException("Only stream resources are supported. " . get_resource_type($value) . " given");
            }
            return $this->quoteBinary(stream_get_contents($value));
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

    /**
     * @inheritDoc
     */
    public function quoteBinary($binary)
    {
        if (strlen($binary) === 0) {
            $hexstr = '';
        } else {
            $hexstr = '\\x' . implode(
                '',
                array_map(
                    function ($chr) {
                        return str_pad(dechex(ord($chr)), 2, '0', STR_PAD_LEFT);
                    },
                    str_split($binary)
                )
            );
        }
        return sprintf("'%s'::bytea", $hexstr);
    }
}
