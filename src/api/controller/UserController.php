<?php
namespace com\icemalta\kahuna\api\controller;

use com\icemalta\kahuna\api\model\AccessLevel;
use com\icemalta\kahuna\api\model\User;

class UserController extends Controller
{
    public static function register(array $params, array $data): void
    {
        // Check if the required input fields are present.
        $requiredFields = ['email', 'password', 'firstName', 'lastName', 'accessLevel'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                self::sendResponse(
                    code: 400,
                    error: [
                        'message' => 'One or more fields are missing.',
                        'requiredFields' => $requiredFields
                    ]
                );
                return;
            }
        }
        // Ensure email address is unique.
        $email = $data['email'];
        $password = $data['password'];
        if (!User::isEmailAvailable($email)) {
            self::sendResponse(code: 400, error: 'Email address given is not available.');
            return;
        }
        // Ensure access level values are legal.
        $accessLevel = AccessLevel::tryFrom($data['accessLevel']);
        if (!$accessLevel) {
            self::sendResponse(
                code: 400,
                error: [
                    'message' => 'Invalid access level specified.',
                    'allowedValues' => AccessLevel::cases()
                ]
            );
            return;
        }
        // Attempt registration.
        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $user = new User($email, $password, $accessLevel, $firstName, $lastName);
        $user = User::save($user);
        if (!$user) {
            self::sendResponse(code: 500, error: 'Create new user failed.');
            return;
        }
        self::sendResponse($user);
    }

    public static function getInfo(array $request, array $data): void
    {
        if (!self::checkToken($data)) {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
            return;
        }
        $user = new User(id: $data['api_user']);
        $user = User::load($user);
        if (!$user) {
            self::sendResponse(code: 500, error: 'Loading user data failed.');
            return;
        }
        self::sendResponse($user);
    }
}