<?php
namespace com\icemalta\kahuna\api\controller;

use \DateTime;
use com\icemalta\kahuna\api\model\Product;
use com\icemalta\kahuna\api\model\CustomerProduct;

class CustomerProductController extends Controller
{
    public static function register(array $request, array $data): void
    {
        // Check if user is authenticated
        if (!self::checkToken($data)) {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
            return;
        }
        // Check if fields are set.
        $serial = $data['serial'] ?? null;
        $purchaseDate = $data['purchaseDate'] ?? null;
        if (!$serial || !$purchaseDate) {
            self::sendResponse(code: 400, error: 'Missing one of `serial` or `purchaseDate` fields.');
            return;
        }
        // Check if product with specified serial number really exists.
        $product = Product::get($serial);
        if (!$product) {
            self::sendResponse(code: 400, error: 'Product with specified serial number does not exist.');
            return;
        }
        // Register customer product.
        $registration = new CustomerProduct($data['api_user'], $serial, new DateTime($purchaseDate));
        $registration = CustomerProduct::save($registration);
        self::sendResponse(code: 201, data: $registration);
    }

    public static function get(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            $customerProduct = new CustomerProduct(customerId: $data['api_user'], productSerial: $data['serial'] ?? null);
            $result = CustomerProduct::get($customerProduct);
            if ($result) {
                error_log('Product found.');
                self::sendResponse($result);
            } else {
                error_log('Product not found');
                self::sendResponse(code: 404, error: 'No product registered.');
            }
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }
}