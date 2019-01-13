<?php
namespace Kedb\Spi\Fake;

class FakeSqlFormatter implements \Kedb\Spi\SpiSqlFormatter
{
    /**
     * @inheritDoc
     */
    public function formatIdent($ident)
    {
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
        return "'" . str_replace("'", "''", $value) . "'";
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
    public function formatBinary($binary)
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
