<?php
declare(strict_types=1);

namespace Mvc;

class Normalizer
{
    /**
     * Normalizes given data. Escapes html special chars if no types stated or explicitly stated not to escape.
     * Other types to normalize are int, float, double, bool, string, trim, xss. Syntax is for example:
     * 'trim|noescape|int'.
     * @param $data string
     * @param $types string|null
     * @return bool|float|int|string
     * @throws \Exception When not valid type found.
     */
    public static function normalize(string $data, $types)
    {
        if ($types == null) {
            return htmlentities($data);
        }

        if (strpos($types, '|') === false) {
            $types .= "|";
        }

        $types = array_filter(explode('|', $types));

        if (!in_array('noescape', $types)) {
            $data = htmlentities($data);
        }

        if (is_array($types)) {
            foreach ($types as $type) {
                switch ($type) {
                    case 'int':
                        $data = (int)$data;
                        break;
                    case 'float':
                        $data = (float)$data;
                        break;
                    case 'double':
                        $data = (double)$data;
                        break;
                    case 'bool':
                        $data = (bool)$data;
                        break;
                    case 'string':
                        $data = (string)$data;
                        break;
                    case 'trim':
                        $data = trim($data);
                        break;
                    default:
                        if ($type != 'noescape') {
                            throw new \Exception('Unsupported normalize type : ' . $type, 500);
                        }
                        break;
                }
            }
        }

        return $data;
    }
}