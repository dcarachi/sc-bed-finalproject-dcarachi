<?php
namespace com\icemalta\kahuna\api\controller;

use com\icemalta\kahuna\api\model\{
    AccessToken,
    AccessLevel,
    User
};

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
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public static function checkToken(array $requestData): bool
    {
        if (!isset($requestData['api_user']) || !isset($requestData['api_token'])) {
            return false;
        }
        $token = new AccessToken($requestData['api_user'], $requestData['api_token']);
        return AccessToken::verify($token);
    }

    public static function checkAdminRights(array $requestData): bool
    {
        if (!isset($requestData['api_user'])) {
            return false;
        }
        $user = User::get($requestData['api_user']);
        if (!$user) {
            return false;
        }
        return $user->getAccessLevel() === AccessLevel::Admin;
    }

    public static function checkFieldsSet(array $requestData, array $fieldNames): array
    {
        return array_filter($fieldNames, fn($field): bool => !isset($requestData[$field]));
    }
}