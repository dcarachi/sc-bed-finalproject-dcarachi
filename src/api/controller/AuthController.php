<?php
namespace com\icemalta\kahuna\api\controller;

class AuthController extends Controller
{
    public static function connectionTest(array $request, array $data): void
    {
        self::sendResponse(['greeting' => 'Welcome to Kahuna API!']);
    }

    public static function login(array $request, array $data): void
    {
        self::sendResponse(code: 501, response: ['error' => 'Method not yet implemented.']);
    }

    public static function logout(array $request, array $data): void
    {
        self::sendResponse(code: 501, response: ['error' => 'Method not yet implemented.']);
    }
}