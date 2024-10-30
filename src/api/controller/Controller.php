<?php
namespace com\icemalta\kahuna\api\controller;

use com\icemalta\kahuna\api\model\AccessToken;

class Controller
{
    public static function sendResponse(mixed $data = null, int $code = 200, mixed $error = null): void
    {
        if (!is_null($data)) {
            $response['data'] = $data;
        }
        if (!is_null($error)) {
            $response['error'] = $error;
        }
        http_response_code($code);
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function checkToken(array $requestData): bool
    {
        if (!isset($requestData['api_user']) || !isset($requestData['api_token'])) {
            return false;
        }
        $token = new AccessToken($requestData['api_user'], $requestData['api_token']);
        return AccessToken::verify($token);
    }
}