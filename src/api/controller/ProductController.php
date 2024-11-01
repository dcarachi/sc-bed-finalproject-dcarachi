<?php
namespace com\icemalta\kahuna\api\controller;

use \DateInterval;
use com\icemalta\kahuna\api\model\Product;

class ProductController extends Controller
{
    public static function add(array $request, array $data): void
    {
        // Check if user is authenticated...
        if (self::checkToken($data)) {
            // ...And is an admin.
            if (self::checkAdminRights($data)) {
                // Check if required input fields are set.
                $required = ['serial', 'name', 'warrantyLength'];
                $missing = self::checkFieldsSet($data, $required);
                if (empty($missing)) {
                    $product = new Product(
                        serial: $data['serial'],
                        name: $data['name'],
                        warrantyLength: new DateInterval('P' . $data['warrantyLength'] . 'Y')
                    );
                    $product = Product::save($product);
                    if ($product) {
                        self::sendResponse($product);
                    } else {
                        self::sendResponse(code: 500, error: 'Save product failed.');
                    }
                } else {
                    self::sendResponse(
                        code: 400,
                        error: ['message' => 'One or more required fields are missing.', 'missingFields' => $missing]
                    );
                }
            } else {
                self::sendResponse(code: 403, error: 'Insufficient access rights to perform this operation.');
            }
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }

    public static function get(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            $product = new Product(id: $request['id']);
            $product = Product::get($product);
            if ($product) {
                self::sendResponse($product);
            } else {
                self::sendResponse(code: 404, error: 'Product not found.');
            }
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }

    public static function getAll(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            $products = Product::getAll();
            self::sendResponse($products);
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }
}