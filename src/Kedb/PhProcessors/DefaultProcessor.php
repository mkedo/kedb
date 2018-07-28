<?php
namespace Kedb\PhProcessors;

use Kedb\Formattable;
use Kedb\KedbException;
use Kedb\PhFormatters\Ident;
use Kedb\PhFormatters\Literal;
use Kedb\PhProcessor;
use Kedb\SqlFormatter;
use Kedb\Template\Placeholder;

class DefaultProcessor implements PhProcessor
{
    /**
     * @var array [ph_type => PhFormatter]
     */
    private $typedPhs;

    public function __construct()
    {
        $this->typedPhs = [
            't' => new Ident()
        ];
    }

    /**
     * @inheritDoc
     */
    public function process(SqlFormatter $formatter, array $placeholders, $params)
    {
        $literal = new Literal();

        $processedParams = [];
        foreach ($placeholders as $ph) {
            /**
             * @var Placeholder $ph
             */
            $phName = $ph->name();
            $phType = $ph->type();
            $phValue = $params[$phName];
            if ($phValue instanceof Formattable) {
                /**
                 * @var Formattable $phValue
                 */
                $value = $phValue->format($formatter);
            } else if (isset($phType)) {
                if (!isset($this->typedPhs[$phType])) {
                    throw new KedbException("Unknown placeholder type: " . $phType);
                }
                $phFormatter = $this->typedPhs[$phType];
                $value = $phFormatter->format($formatter, $phValue);
            } else {
                $value = $literal->format($formatter, $phValue);
            }
            $processedParams[$phName] = $value;
        }

        return $processedParams;
    }
}
