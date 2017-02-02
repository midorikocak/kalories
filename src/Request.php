<?php

namespace MidoriKocak;

/**
 * Class Request
 *
 * A simple and optimized version of LornaJane Request class
 *
 * @url https://lornajane.net/posts/2008/php-rest-server-part-1-of-3
 * @package MidoriKocak
 */
class Request
{
    public $urlElements;
    public $verb;
    public $parameters;

    public function __construct()
    {
        $this->verb = $_SERVER['REQUEST_METHOD'];
        $elements = isset($_SERVER['PATH_INFO']) ? array_filter(explode('/', $_SERVER['PATH_INFO'])) : [];
        $this->sanitize($elements);

        $this->urlElements = $elements;
        $this->parseIncomingParams();
        // initialise json as default format
        $this->format = 'json';
        if (isset($this->parameters['format'])) {
            $this->format = $this->parameters['format'];
        }
        return true;
    }

    private function sanitize(&$array)
    {
        $filter = function (&$value) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        };
        array_walk_recursive($array, $filter);
    }

    public function parseIncomingParams()
    {
        $parameters = [];

        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $values);
            $this->sanitize($values);
            $parameters = $values;
        }

        $body = file_get_contents("php://input");
        $content_type = $_SERVER['CONTENT_TYPE'] ?? false;
        if ($content_type == "application/json") {
            $body_params = json_decode($body);
            if ($body_params) {
                foreach ($body_params as $param_name => $param_value) {
                    $param_name = htmlspecialchars($param_name);
                    $param_value = htmlspecialchars($param_value);
                    $parameters[$param_name] = $param_value;
                }
            }
            $this->format = "json";
        }
        $this->parameters = $parameters;
    }
}