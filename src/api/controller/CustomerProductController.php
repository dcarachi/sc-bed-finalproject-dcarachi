<?php
namespace com\icemalta\kahuna\api\controller;

use \DateTime;
use com\icemalta\kahuna\api\model\Product;
use com\icemalta\kahuna\api\model\CustomerProduct;

class CustomerProductController extends Controller
{
    /**
     * Register a new product that the customer purchased. Requires that the user is authenticated for the operation to be allowed.
     * @param array $request Ignored.
     * @param array $data An associative array with token fields set, and fields 'serial', and 'purchaseDate' set.
     * @return void
     */
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
        $product = new Product($serial);
        $result = Product::get($product);
        if (!isset($result)) {
            self::sendResponse(code: 400, error: 'Product with specified serial number does not exist.');
            return;
        }
        // Register customer product.
        $registration = new CustomerProduct($data['api_user'], $serial, $purchaseDate);
        $registration = CustomerProduct::save($registration);
        self::sendResponse(code: 201, data: $registration);
    }

    /**
     * Returns a summary of the user's products registered, or the details of a particular product registered if the serial key is set.
     * Requires that the user is authenticated for the operation to be allowed.
     * @param array $request
     * @param array $data
     * @return void
     */
    public static function get(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            if (isset($data['serial'])) {
                // Return product details of a given product serial.
                $custProduct = new CustomerProduct(customerId: $data['api_user'], productSerial: $data['serial']);
                $result = CustomerProduct::getProductInfo($custProduct);
                if ($result) {
                    self::sendResponse($result);
                } else {
                    self::sendResponse(data: false);
                }
            } else {
                // Return a summary of the registered products.
                $custProduct = new CustomerProduct(customerId: $data['api_user']);
                $result = CustomerProduct::getProducts($custProduct);
                self::sendResponse($result);
            }
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }
}