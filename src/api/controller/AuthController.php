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

    public static function verifyToken(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            self::sendResponse(['valid' => true, 'token' => $data['api_token']]);
        } else {
            self::sendResponse(['valid' => false, 'token' => $data['api_token'] ?? null]);
        }
    }
}