<?php
namespace com\icemalta\kahuna\api\controller;

use \DateTime;
use com\icemalta\kahuna\api\model\Product;
use com\icemalta\kahuna\api\model\UserPurchase;

class UserPurchaseController extends Controller
{
    public static function register(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            $required = ['serial', 'purchaseDate'];
            $missing = self::checkFieldsSet($data, $required);
            if (empty($missing)) {
                // Check if serial number refers to a valid product.
                $product = Product::getBySerial($data['serial']);
                if ($product) {
                    $purchase = new UserPurchase(
                        userId: $data['api_user'],
                        productId: $product->getId(),
                        purchaseDate: new DateTime($data['purchaseDate'])
                    );
                    $purchase = UserPurchase::save($purchase);
                    self::sendResponse(code: 201, data: $purchase);
                } else {
                    self::sendResponse(code: 400, error: 'Product with specified serial number does not exist.');
                }
            } else {
                self::sendResponse(
                    code: 400,
                    error: ['message' => 'One or more required fields are missing.', 'missingFields' => $missing]
                );
            }
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }

    public static function get(array $request, array $data): void
    {
        self::sendResponse(code: 501, error: 'Method not yet implemented.');
    }

    public static function getAll(array $request, array $data): void
    {
        if (self::checkToken($data)) {
            $purchases = UserPurchase::getAll();
            self::sendResponse($purchases);
        } else {
            self::sendResponse(code: 401, error: 'Missing, invalid, or expired token.');
        }
    }
}