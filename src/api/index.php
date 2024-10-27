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

/** Basic Test Route ------------------------------------------------------------------------------ */
$router->map("GET", "/", "AuthController#test", "test");
/** ----------------------------------------------------------------------------------------------- */

$match = $router->match();
if (is_array($match)) {
    $target = explode('#', $match['target']);
    $class = $target[0];
    $action = $target[1];
    $params = $match['params'];
    $requestData = ApiHelper::getRequestData();
    call_user_func_array(__NAMESPACE__ . "\\controller\\$class::$action", [$params, $requestData]);
} else {
    Controller::sendResponse(code: 404, error: 'Method not found.');
}