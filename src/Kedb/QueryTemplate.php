<?php
namespace Kedb;

use Kedb\PhFormatters\Ident;
use Kedb\PhFormatters\Literal;
use Kedb\Template\Placeholder;
use Kedb\Template\PlaceholderTemplate;

class QueryTemplate
{
    /**
     * @var PlaceholderTemplate
     */
    private $template;

    /**
     * @var array [ph_type => PhFormatter]
     */
    private $phTypes;

    /**
     * @param $templateString
     * @param array $phTypes
     */
    public function __construct($templateString, array $phTypes = [])
    {
        $this->template = new Template\PlaceholderTemplate($templateString);
        $this->phTypes = array_merge(
            [
                '' => new Literal(),
                't' => new Ident(),
            ],
            $phTypes
        );
    }

    /**
     * @param SqlFormatter $formatter
     * @param array $params
     * @throws KedbException
     */
    public function fetch(SqlFormatter $formatter, array $params)
    {
        $placeholders = $this->template->getPlaceholders();

        $formattedParams = [];
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
            } elseif (isset($this->phTypes[$phType])) {
                $phFormatter = $this->phTypes[$phType];
                $value = $phFormatter->format($formatter, $phValue);
            } else {
                throw new KedbException("Unknown placeholder type: " . $phType);
            }
            $formattedParams[$phName] = $value;
        }
        return $this->template->fetch($formattedParams);
    }
}
