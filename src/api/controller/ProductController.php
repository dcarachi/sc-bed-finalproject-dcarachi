<?php
namespace com\icemalta\kahuna\api\controller;

use \DateInterval;
use \DateMalformedIntervalStringException;
use com\icemalta\kahuna\api\model\Product;

class ProductController extends Controller
{
    /**
     * Adds a new product to the list. Requires that the user is authenticated and has Admin AccessLevel for the operation to be allowed.
     * @param array $request Ignored.
     * @param array $data An associative array with token fields set, and fields `serial`, `name`, and `warrantyLength` set.
     * @return void
     */
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
        // Ensure warrantyLength is a valid DateInterval.
        try {
            $product = new Product($serial, $name, new DateInterval($warrantyLength));
            // Ensure product does not already exist in the DB.
            if (Product::exists($product)) {
                self::sendResponse(code: 400, error: "Product with serial number `{$product->getSerial()}` already exists.");
                return;
            }
            $product = Product::save($product);
            if ($product) {
                self::sendResponse(code: 201, data: $product);
            } else {
                self::sendResponse(code: 500, error: 'Failed to insert or update product.');
            }
        } catch (DateMalformedIntervalStringException $e) {
            self::sendResponse(code: 400, error: $e->getMessage());
        }
    }

    /**
     * Obtains the entire product listing, or a single product if a serial is specified.
     * Requires that the user is authenticated for the operation to be allowed.
     * @param array $request Ignored.
     * @param array $data An assoicative array with token fields set.
     * If a `serial` value is included, only the product with that particular serial number is returned if it exists.
     * @return void
     */
    public static function get(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            if (isset($data['serial'])) {
                $products = Product::get(new Product(serial: $data['serial'])) ?? false;
            } else {
                $products = Product::getAll();
            }
            self::sendResponse($products);
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }
}