<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '1');

use App\Config\Database;
use App\Core\Router;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Services\ProductService;
use App\Services\CategoryService;
use App\Controllers\ProductController;
use App\Controllers\CategoryController;
use App\Middleware\ErrorHandler;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->load();

ErrorHandler::register();

header('Content-Type: application/json; charset=utf-8');

$db = Database::connection();

$productRepository = new ProductRepository($db);
$categoryRepository = new CategoryRepository($db);

$productService = new ProductService($productRepository);
$categoryService = new CategoryService($categoryRepository);

$productController = new ProductController($productService);
$categoryController = new CategoryController($categoryService);

$router = new Router();

$registerRoutes = require dirname(__DIR__) . '/routes/api.php';
$registerRoutes(
    $router,
    $productController,
    $categoryController
);

$uri = $_SERVER['REQUEST_URI'];
$script = $_SERVER['SCRIPT_NAME'];

if (basename($script) === 'index.php') {
    $base = rtrim(str_replace('\\', '/', dirname($script)), '/');

    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
}

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $uri
);