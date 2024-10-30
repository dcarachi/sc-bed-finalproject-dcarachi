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

    public static function login(array $request, array $data): void
    {
        if (!isset($data['email']) || !isset($data['password'])) {
            self::sendResponse(code: 400, error: 'Missing email and/or password fields.');
            return;
        }
        $email = $data['email'];
        $password = $data['password'];
        $user = new User($email, $password);
        $user = User::authenticate($user);
        if (!$user) {
            self::sendResponse(code: 401, error: 'Login failed.');
            return;
        }
        $token = new AccessToken($user->getId());
        $token = AccessToken::save($token);
        if (!$token) {
            self::sendResponse(code: 500, error: 'Unable to save token.');
            return;
        }
        self::sendResponse(['user' => $user->getId(), 'token' => $token->getToken()]);
    }

    public static function logout(array $request, array $data): void
    {
        if (!self::checkToken($data)) {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
            return;
        }
        $userId = $data['api_user'];
        $token = new AccessToken($userId);
        $deleted = AccessToken::delete($token);
        if (!$deleted) {
            self::sendResponse(code: 500, error: 'Log out failed. Unable to delete token(s).');
            return;
        }
        self::sendResponse('You have been logged out successfully.');
    }

    public static function verifyToken(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            self::sendResponse(['valid' => true, 'token' => $data['api_token']]);
        } else {
            self::sendResponse(['valid' => false, 'token' => $data['api_token']]);
        }
    }
}