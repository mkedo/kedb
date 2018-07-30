<?php
namespace Kedb\Spi\Common;

use Kedb\Spi\SpiSqlFormatter;
use Kedb\SqlFormatter;

class CmSqlFormatter implements SqlFormatter
{
    /**
     * @var SpiSqlFormatter
     */
    private $formatter;

    /**
     * @param SpiSqlFormatter $spiFormatter
     */
    public function __construct(SpiSqlFormatter $spiFormatter)
    {
        $this->formatter = $spiFormatter;
    }

    /**
     * @inheritDoc
     */
    public function quoteLiteral($value)
    {
        if (is_null($value)) {
            $result = $this->formatter->formatNull();
        } elseif (is_int($value)) {
            $result = $this->formatter->formatInt($value);
        } elseif (is_float($value)) {
            $result = $this->formatter->formatFloat($value);
        } elseif (is_bool($value)) {
            $result = $this->formatter->formatBool($value);
        } elseif (is_string($value)) {
            $result = $this->formatter->formatString($value);
        } else if (is_resource($value)) {
            if (get_resource_type($value) !== 'stream') {
                throw new KedbException("Only stream resources are supported. " . get_resource_type($value) . " given");
            }
            $bin = stream_get_contents($value);
            if ($bin === false) {
                throw new KedbException("Could not read stream");
            }
            $result = $this->formatter->formatBinary($bin);
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
            return $this->formatter->formatNull();
        }
        return $this->formatter->formatIdent($ident);
    }

    /**
     * @inheritDoc
     */
    public function quoteBinary($binary)
    {
        if (is_null($binary)) {
            return $this->formatter->formatNull();
        }
        return $this->formatter->formatBinary($binary);
    }
}
