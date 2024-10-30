<?php
namespace com\icemalta\kahuna\api\controller;

use com\icemalta\kahuna\api\model\{
    User,
    Product
};

class ProductController extends Controller
{
    public static function add(array $request, array $data): void
    {
        if (!self::checkToken($data)) {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
            return;
        }
        if (!self::checkAdminRights($data)) {
            self::sendResponse(code: 403, error: 'Insufficient access rights to perform this operation.');
            return;
        }
        $required = ['serial', 'name', 'warrantyLength'];
        $missing = self::checkInputSet($data, $required);
        if (!empty($missing)) {
            self::sendResponse(
                code: 400,
                error: ['message' => 'One or more required fields are missing.', 'missingFields' => $missing]
            );
            return;
        }
        $product = new Product(
            serial: $data['serial'],
            name: $data['name'],
            warrantyLength: $data['warrantyLength']
        );
        $product = Product::save($product);
        self::sendResponse($product);
    }

    public static function get(array $request, array $data): void
    {
        if (!self::checkToken($data)) {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
            return;
        }
        $product = new Product(id: $request['id']);
        $product = Product::load($product);
        if (!$product) {
            self::sendResponse(code: 404, error: 'Product not found.');
            return;
        }
        self::sendResponse($product);
    }

    public static function getAll(array $request, array $data): void
    {
        if (!self::checkToken($data)) {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
            return;
        }
        $products = Product::loadAll();
        self::sendResponse($products);
    }
}