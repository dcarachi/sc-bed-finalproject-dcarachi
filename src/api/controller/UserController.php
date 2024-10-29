<?php
namespace com\icemalta\kahuna\api\controller;

use com\icemalta\kahuna\api\model\AccessLevel;
use com\icemalta\kahuna\api\model\User;

class UserController extends Controller
{
    public static function register(array $params, array $data): void
    {
        $requiredData = ['email', 'password', 'firstName', 'lastName', 'accessLevel'];
        if (self::checkFieldsSet($data, $requiredData)) {
            $email = $data['email'];
            $password = $data['password'];
            $firstName = $data['firstName'];
            $lastName = $data['lastName'];
            $accessLevel = AccessLevel::tryFrom($data['accessLevel']);

            if ($accessLevel) {
                if (User::isEmailAvailable($email)) {
                    $user = new User($email, $password, $accessLevel, $firstName, $lastName);
                    $user = User::save($user);
                    if ($user) {
                        self::sendResponse(['user' => $user]);
                    } else {
                        self::sendResponse(code: 500, response: ['error' => 'Failed to create new user.']);
                    }
                } else {
                    self::sendResponse(code: 400, response: ['error' => 'Email address specified is not available.']);
                }
            } else {
                $response['error'] = 'Invalid access level specified';
                $response['allowedValues'] = AccessLevel::cases();
                self::sendResponse(code: 400, response: $response);
            }
        } else {
            $response['error'] = 'Invalid request. One or more fields are missing.';
            $response['requiredFields'] = $requiredData;
            self::sendResponse(code: 400, response: $response);
        }
    }

    public static function getInfo(array $request, array $token): void
    {
        self::sendResponse(code: 501, response: ['error' => 'Method not yet implemented.']);
    }
}