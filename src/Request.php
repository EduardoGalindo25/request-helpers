<?php

namespace gabogalro\requestHelpers;

class Request
{
    /**
     * Summary of headers
     * @return array
     */
    public static function headers(): array
    {
        return getallheaders();
    }

    /**
     * Get the JSON body of the request.
     *
     * @return array
     */
    public static function json()
    {
        $headers = self::headers();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $isJson = isset($headers['Content-Type']) && stripos($headers['Content-Type'], 'application/json') !== false;
        if ($isJson && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $rawInput = file_get_contents('php://input');
            return json_decode($rawInput, true) ?? [];
        }
        return [];
    }
    /**
     * Get the Bearer token from the Authorization header.
     * @return string|null
     */
    public static function getBearerToken(): ?string
    {
        $headers = self::headers();

        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
            return $headers['Authorization'];
        }

        return null;
    }

    /**
     * Get the parameters from the request.
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */

    public static function parameter(?string $key = null, $default = null)
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($method, ['GET', 'PUT', 'PATCH', 'DELETE'])) {
            return $key ? $default : [];
        }

        $headers = self::headers();
        if (isset($headers['Content-Type']) && stripos($headers['Content-Type'], 'application/json') !== false) {
            return $key ? $default : [];
        }

        $params = $_GET ?? [];

        if ($key === null) {
            return $params;
        }

        return $params[$key] ?? $default;
    }



    /**
     * Get the form data of the request.
     *
     * @return array
     * */
    public static function formData(?string $key = null, $default = null)
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($method, ['POST', 'PUT', 'PATCH'])) {
            return $key ? $default : [];
        }

        $data = $_POST ?? [];

        // Función recursiva para "aplanar" arrays con [0] => valor
        $normalize = function (array $arr) use (&$normalize) {
            $result = [];
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    // si el array tiene keys numericas 0,1,2... lo dejamos como array simple
                    $isSequential = array_keys($v) === range(0, count($v) - 1);
                    $result[$k] = $isSequential ? $v : $normalize($v);
                } else {
                    $result[$k] = $v;
                }
            }
            return $result;
        };

        $data = $normalize($data);

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    /**
     * Get uploaded files.
     *
     * @return array
     */
    public static function files()
    {
        return $_FILES ?? [];
    }
}
