<?php
namespace com\icemalta\kahuna\api\controller;

use com\icemalta\kahuna\api\model\AccessLevel;
use com\icemalta\kahuna\api\model\User;

class UserController extends Controller
{
    public static function register(array $params, array $data): void
    {
        // Check that the required input fields are present.
        $required = ['email', 'password', 'firstName', 'lastName', 'accessLevel'];
        $missing = self::checkFieldsSet($data, $required);
        if (empty($missing)) {
            // Ensure access level values are legal.
            $accessLevel = AccessLevel::tryFrom($data['accessLevel']);
            if ($accessLevel) {
                $email = $data['email'];
                $password = $data['password'];
                $firstName = $data['firstName'];
                $lastName = $data['lastName'];
                $user = new User($email, $password, $accessLevel, $firstName, $lastName);
                // Ensure email address is unique.
                if (User::isEmailAvailable($user)) {
                    // Register user
                    $user = User::save($user);
                    self::sendResponse($user);
                } else {
                    self::sendResponse(code: 400, error: 'Email address given is not available.');
                }
            } else {
                self::sendResponse(
                    code: 400,
                    error: ['message' => 'Invalid access level specified.', 'allowedValues' => AccessLevel::cases()]
                );
            }
        } else {
            self::sendResponse(
                code: 400,
                error: ['message' => 'One or more required fields are missing.', 'missingFields' => $missing]
            );
        }
    }

    public static function getInfo(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            $user = new User(id: $data['api_user']);
            $user = User::load($user);
            self::sendResponse($user);
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }
}