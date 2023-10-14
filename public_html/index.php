<?php
$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "https://app.hesabix.ir" || $http_origin == "http://insider.hesabix.ir")
{
    header("Access-Control-Allow-Origin: $http_origin");
}
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

use App\Kernel;
require_once dirname(__DIR__).'/hesabixCore/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};