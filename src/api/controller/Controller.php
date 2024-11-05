<?php
namespace com\icemalta\kahuna\api\controller;

use com\icemalta\kahuna\api\model\{
    AccessToken,
    AccessLevel,
    User
};

class Controller
{
    /**
     * Sends an HTTP response to the client.
     * @param mixed $data The data payload to send as body.
     * @param int $code The HTTP status code, having a default value of `200`.
     * @param mixed $error The error message to send as payload, to indicate an operation failure.
     * @return void
     */
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

    /**
     * Checks whether the user is logged in by validating the token fields `api_user` and `api_token`.
     * @param array $requestData An associate array with the user id set as value for `api_user` and the token string set as value for `api_token`.
     * @return bool Returns `true` if the token is valid, `false` otherwise.
     */
    public static function checkToken(array $requestData): bool
    {
        if (!isset($requestData['api_user']) || !isset($requestData['api_token'])) {
            return false;
        }
        $token = new AccessToken($requestData['api_user'], $requestData['api_token']);
        return AccessToken::verify($token);
    }

    /**
     * Check if a logged in user has Admin AccessLevel.
     * @param array $requestData An associative array with the key `api_user` and the user id to search for as its value.
     * @return bool Returns `true` if the user has such rights, `false` otherwise.
     */
    public static function checkAdminRights(array $requestData): bool
    {
        if (!isset($requestData['api_user'])) {
            return false;
        }
        $user = new User(id: $requestData['api_user']);
        $user = User::get($user);
        if (is_null($user)) {
            return false;
        }
        return $user->getAccessLevel() === AccessLevel::Admin;
    }
}