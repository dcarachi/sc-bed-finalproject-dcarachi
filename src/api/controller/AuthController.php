<?php
namespace com\icemalta\kahuna\api\controller;

use com\icemalta\kahuna\api\model\User;
use com\icemalta\kahuna\api\model\AccessToken;

class AuthController extends Controller
{
    public static function connectionTest(array $request, array $data): void
    {
        self::sendResponse('Welcome to Kahuna API!');
    }

    /**
     * Authenticates a user.
     * @param array $params Ignored.
     * @param array $data An associative array with email and password fields set.
     * @return void
     */
    public static function login(array $params, array $data): void
    {
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        if ($email && $password) {
            $user = User::authenticate($email, $password);
            if ($user) {
                $token = new AccessToken(userId: $user->getId());
                $token = AccessToken::save($token);
                self::sendResponse(data: ['user' => $user->getId(), 'token' => $token->getToken()]);
            } else {
                self::sendResponse(code: 401, error: 'Login Failed.');
            }
        } else {
            self::sendResponse(code: 400, error: 'Missing email or password fields.');
        }
    }

    /**
     * Logs out a user. Requires that the user is authenticated for the operation to be allowed.
     * @param array $params
     * @param array $data An associative array with the token fields `api_user` and `api_token` set.
     * @return void
     */
    public static function logout(array $params, array $data): void
    {
        if (self::checkToken($data)) {
            $userId = $data['api_user'];
            $token = new AccessToken(userId: $userId);
            $token = AccessToken::delete($token);
            self::sendResponse(data: ['message' => 'You have been logged out.']);
        } else {
            self::sendResponse(code: 403, error: 'Missing, invalid or expired token.');
        }
    }

    /**
     * Checks if the token is still valid and not expired.
     * @param array $request Ignored.
     * @param array $data An associative array with the token fields `api_user` and `api_token` set.
     * @return void
     */
    public static function verifyToken(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            self::sendResponse(['valid' => true, 'token' => $data['api_token']]);
        } else {
            self::sendResponse(['valid' => false, 'token' => $data['api_token'] ?? null]);
        }
    }
}