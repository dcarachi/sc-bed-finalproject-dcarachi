<?php
namespace com\icemalta\kahuna\api\controller;

class Controller
{
    public static function sendResponse(mixed $data = null, int $code = 200, mixed $error = null): void
    {
        if (!is_null($data)) {
            $response['data'] = $data;
        }
        if (!is_null($error)) {
            $response['error'] = [
                'message' => $error,
                'code' => $code
            ];
        }
        http_response_code($code);
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}