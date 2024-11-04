<?php
namespace com\icemalta\kahuna\api\controller;

use com\icemalta\kahuna\api\model\AccessLevel;
use com\icemalta\kahuna\api\model\User;

class UserController extends Controller
{
    public static function register(array $params, array $data): void
    {
        // Ensure required fields are set.
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $firstName = $data['firstName'] ?? null;
        $lastName = $data['lastName'] ?? null;
        $accessLevel = $data['accessLevel'] ?? null;
        if (!$email || !$password || !$firstName || !$lastName || !$accessLevel) {
            self::sendResponse(
                code: 400,
                error: 'Missing one of: `email`, `password`, `firstName`, `lastName`, or `accessLevel` fields.'
            );
            return;
        }
        // Ensure a valid access level has been set.
        if (!AccessLevel::tryFrom($accessLevel)) {
            self::sendResponse(code: 400, error: 'Invalid access level specified. Must be one of `admin` or `client`.');
            return;
        }
        // Check email address is unique.
        if (!User::isEmailAvailable($email)) {
            self::sendResponse(code: 400, error: 'Email address given is not available.');
            return;
        }
        // Register user
        $user = new User($email, $password, AccessLevel::from($accessLevel), $firstName, $lastName);
        $user = User::save($user);
        if ($user) {
            self::sendResponse(code: 201, data: $user);
        } else {
            self::sendResponse(code: 500, error: 'Failed to insert or update user.');
        }
    }

    public static function getInfo(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            $user = User::get($data['api_user']);
            self::sendResponse($user);
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }
}