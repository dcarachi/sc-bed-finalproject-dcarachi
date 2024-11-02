<?php
namespace com\icemalta\kahuna\api\controller;

use \DateInterval;
use \DateMalformedIntervalStringException;
use com\icemalta\kahuna\api\model\Product;

class ProductController extends Controller
{
    public static function add(array $request, array $data): void
    {
        // Check if user is authenticated...
        if (!self::checkToken($data)) {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
            return;
        }
        // ...And is an admin.
        if (!self::checkAdminRights($data)) {
            self::sendResponse(code: 403, error: 'Insufficient access rights to perform this operation.');
            return;
        }
        // Check serial, name, and warrantyLength are set.
        $serial = $data['serial'] ?? null;
        $name = $data['name'] ?? null;
        $warrantyLength = $data['warrantyLength'] ?? null;
        if (!$serial || !$name || !$warrantyLength) {
            self::sendResponse(code: 400, error: 'Missing one of `serial`, `name`, or `warrantyLength` fields.');
            return;
        }
        // Verify warrantyLength is a valid ISO 8601 value.
        try {
            $warrantyLength = new DateInterval($warrantyLength);
            $product = new Product($serial, $name, $warrantyLength);
            $product = Product::save($product);
            self::sendResponse(code: 201, data: $product);
        } catch (DateMalformedIntervalStringException $e) {
            self::sendResponse(code: 400, error: 'Field warrantyLength must be in ISO 8601 format.');
        }
    }

    public static function get(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            if (isset($data['serial'])) {
                $products = Product::get($data['serial']);
            } else {
                $products = Product::getAll();
            }
            self::sendResponse($products);
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }
}