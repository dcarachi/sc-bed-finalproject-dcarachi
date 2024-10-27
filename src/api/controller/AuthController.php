<?php
namespace com\icemalta\kahuna\api\controller;

class AuthController extends Controller
{
    public static function test(): void
    {
        self::sendResponse('Welcome to Kahuna API!');
    }
}