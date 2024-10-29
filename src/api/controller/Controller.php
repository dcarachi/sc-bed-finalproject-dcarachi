<?php
namespace com\icemalta\kahuna\api\controller;

class Controller
{
    public static function sendResponse(mixed $response, int $code = 200): void
    {
        http_response_code($code);
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function checkFieldsSet(array $input, array $fieldsWanted): bool
    {
        foreach ($fieldsWanted as $key) {
            if (!isset($input[$key])) {
                return false;
            }
        }
        return true;
    }
}