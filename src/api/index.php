<?php
namespace com\icemalta\kahuna\api;

use \AltoRouter;
use com\icemalta\kahuna\api\helper\ApiHelper;
use com\icemalta\kahuna\api\controller\Controller;

require 'vendor/autoload.php';

/** BASIC SETTINGS ================================================================================ */
const BASE_URI = "/kahuna/api";
header("Content-Type: application/json; charset=UTF-8");
ApiHelper::handleCors();
/** =============================================================================================== */

$router = new AltoRouter();
$router->setBasePath(BASE_URI);

/** Basic Test Route -------------------------------------------------------------------------------------------------- */
$router->map("GET", "/", "AuthController#connectiontest", "test");
/** ------------------------------------------------------------------------------------------------------------------- */

/** User Management Routes -------------------------------------------------------------------------------------------- */
$router->map("POST", "/user", "UserController#register", "user_register");
$router->map("GET", "/user", "UserController#getInfo", "user_get_info");
/** ------------------------------------------------------------------------------------------------------------------- */

/** User Authentication Routes ---------------------------------------------------------------------------------------- */
$router->map("POST", "/login", "AuthController#login", "auth_login");
$router->map("POST", "/logout", "AuthController#logout", "auth_logout");
$router->map("GET", "/token", "AuthController#verifyToken", "auth_verify_token");
/** ------------------------------------------------------------------------------------------------------------------- */

/** Customer Purchase Registration Routes ------------------------------------------------------------------------------ */
$router->map("POST", "/user/purchase", "UserPurchaseController#register", "user_register_purchase");
$router->map("GET", "/user/purchase/[i:id]", "UserPurchaseController#get", "user_get_purchase");
$router->map("GET", "/user/purchase", "UserPurchaseController#getAll", "user_get_all_purchases");
/** ------------------------------------------------------------------------------------------------------------------- */

/** Product Management Routes ----------------------------------------------------------------------------------------- */
$router->map("POST", "/product", "ProductController#add", "product_add");
$router->map("GET", "/product/[i:id]", "ProductController#get", "product_get");
$router->map("GET", "/product", "ProductController#getAll", "product_get_all" );
/** ------------------------------------------------------------------------------------------------------------------- */

$match = $router->match();
if (is_array($match)) {
    $target = explode('#', $match['target']);
    $class = $target[0];
    $action = $target[1];
    $params = $match['params'];
    $requestData = ApiHelper::getRequestData();
    if (isset($_SERVER['HTTP_X_API_USER'])) {
        $requestData['api_user'] = $_SERVER['HTTP_X_API_USER'];
    }
    if (isset($_SERVER['HTTP_X_API_KEY'])) {
        $requestData['api_token'] = $_SERVER['HTTP_X_API_KEY'];
    }
    call_user_func_array(__NAMESPACE__ . "\\controller\\$class::$action", [$params, $requestData]);
} else {
    Controller::sendResponse(code: 404, error: 'Method not found.');
}