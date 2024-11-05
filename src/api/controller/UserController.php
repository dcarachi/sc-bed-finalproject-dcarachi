<?php
namespace com\icemalta\kahuna\api\controller;

use com\icemalta\kahuna\api\model\AccessLevel;
use com\icemalta\kahuna\api\model\User;

class UserController extends Controller
{
    /**
     * Registers a new Administrator or Customer.
     * @param array $params Ignored.
     * @param array $data An associative array with fields 'email', 'password', 'firstName', 'lastName', and 'accessLevel' set.
     * @return void
     */
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
        // Check if email address is unique.
        $user = new User($email, $password, AccessLevel::from($accessLevel), $firstName, $lastName);
        if (!User::isEmailAvailable($user)) {
            self::sendResponse(code: 400, error: 'Email address given is not available.');
            return;
        }
        // Register user
        $user = User::save($user);
        if ($user) {
            self::sendResponse(code: 201, data: $user);
        } else {
            self::sendResponse(code: 500, error: 'Failed to insert or update user.');
        }
    }

    /**
     * Gets the logged in user's details. Requires that the user is authenticated for the operation to be allowed.
     * @param array $request Ignored.
     * @param array $data An associative array with token fields set.
     * @return void
     */
    public static function getInfo(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            $user = new User(id: $data['api_user']);
            $user = User::get($user);
            self::sendResponse($user);
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }
}