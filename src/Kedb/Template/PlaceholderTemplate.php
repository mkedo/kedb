<?php
namespace Kedb\Template;

use \Kedb\KedbException;

/**
 * Compiles template
 * @todo: syntax
 * ?
 * ?t
 * \?
 * ?:name
 * ?t:name
 */
class PlaceholderTemplate
{
    /**
     * @var array
     */
    private $templateVector;

    /**
     * @var array
     */
    private $templateMap;

    /**
     * @var string
     */
    private $template;

    /**
     * PlaceholderTemplate constructor.
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * @param array $data [ name|index => string ]
     * @return string
     * @throws \Exception
     */
    public function fetch(array $data) {
        if ($this->templateVector === null) {
            $this->compile();
        }
        $templateVector = $this->templateVector;
        foreach ($this->templateMap as $ph) {
            $phName = $ph['name'];
            $idx = $ph['idx'];
            if (!array_key_exists($phName, $data)) {
                throw new KedbException("No data provided for placeholder $phName");
            }
            $templateVector[$idx] = $data[$phName];
        }
        return implode('', $templateVector);
    }

    /**
     * @return Placeholder[]
     */
    public function getPlaceholders()
    {
        if ($this->templateVector === null) {
            $this->compile();
        }
        $placeholders = [];
        foreach ($this->templateMap as $value) {
            $placeholders[] = new Placeholder($value['name'], $value['type']);
        }
        return $placeholders;
    }

    private function compile()
    {
        $template = $this->template;
        $regex = '/(\\\?)\?([a-zA-Z-_]+[a-zA-Z-_0-9]*)?(\:[a-zA-Z-_]+[a-zA-Z-_0-9]*)?/';
        if ($matchesCount = preg_match_all($regex, $template, $matches, PREG_OFFSET_CAPTURE)) {
            $templateVector = [];
            $templateMap = [];

            $lastPhOffset = 0;
            $tplLen = strlen($template);

            $id = 0;
            for ($i = 0; $i < $matchesCount; $i++) {
                $match = $matches[0][$i][0];
                $offset = $matches[1][$i][1];
                $backslash = $matches[1][$i][0];
                $phLen = strlen($match);

                $precedingPart = substr($template, $lastPhOffset, $offset - $lastPhOffset);
                $templateVector []= $precedingPart;

                if (!empty($backslash)) {
                    $templateVector []= substr($match, 1);
                } else {
                    $phType = isset($matches[2][$i][0]) && is_array($matches[2][$i])
                        ? $matches[2][$i][0]
                        : '';
                    $phName = isset($matches[3][$i][0])
                        ? substr($matches[3][$i][0], 1)
                        : $id++;
                    $templateVector []= '';
                    $templateMap[] = [
                        'name' => $phName,
                        'idx' => count($templateVector) - 1,
                        'type' => $phType
                    ];
                }
                $lastPhOffset = $offset + $phLen;
            }
            if ($tplLen > $lastPhOffset) {
                $endingPart = substr($template, $lastPhOffset);
                $templateVector []= $endingPart;
            }
            $this->templateVector = $templateVector;
            $this->templateMap = $templateMap;
        } else {
            $this->templateVector = [$template];
            $this->templateMap = [];
        }
    }
}
