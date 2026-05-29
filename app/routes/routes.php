<?php

declare(strict_types=1);
use Slim\Factory\AppFactory;
$app = AppFactory::create();
require __DIR__ . '/../vendor/autoload.php';

$app->get('/', App\Controller\Home::class . ':home')->add(App\Middleware\Middleware::web());
$app->get('/home', App\Controller\Home::class . ':home')->add(App\Middleware\Middleware::web());
$app->get('/login', App\Controller\Login::class . ':login')->add(App\Middleware\Middleware::web());

$app->group('/authentication', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->post('/google', App\Controller\Login::class . ':google');
    $group->post('/auth', App\Controller\Login::class . ':authenticate');
    $group->post('/preregister', App\Controller\Login::class . ':preRegister');
    $group->get('/logout', App\Controller\Login::class . ':logout');
});

$app->group('/pais', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', App\Controller\Country::class . ':list');
    $group->get('/detalhes/{id}', App\Controller\Country::class . ':details');
    $group->get('/detalhes', App\Controller\Country::class . ':details');
    $group->post('/insert', App\Controller\Country::class . ':insert');
    $group->post('/update', App\Controller\Country::class . ':update');
    $group->post('/delete', App\Controller\Country::class . ':delete');
    $group->post('/listingdata', App\Controller\Country::class . ':listingdata');
});

$app->group('/usuario', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', App\Controller\User::class . ':list');
    $group->get('/detalhes/{id}', App\Controller\User::class . ':details');
    $group->get('/detalhes', App\Controller\User::class . ':details');
    $group->post('/insert', App\Controller\User::class . ':insert');
    $group->post('/update', App\Controller\User::class . ':update');
    $group->post('/delete', App\Controller\User::class . ':delete');
    $group->post('/listingdata', App\Controller\User::class . ':listingdata');
});


$app->group('/cliente', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', App\Controller\Customer::class . ':list');
    $group->get('/detalhes/{id}', App\Controller\Customer::class . ':details');
    $group->get('/detalhes', App\Controller\Customer::class . ':details');
    $group->post('/insert', App\Controller\Customer::class . ':insert');
    $group->post('/update', App\Controller\Customer::class . ':update');
    $group->post('/delete', App\Controller\Customer::class . ':delete');
    $group->post('/listingdata', App\Controller\Customer::class . ':listingdata');
});

$app->group('/fornecedor', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', App\Controller\Supplier::class . ':list');
    $group->get('/detalhes/{id}', App\Controller\Supplier::class . ':details');
    $group->get('/detalhes', App\Controller\Supplier::class . ':details');
    $group->post('/insert', App\Controller\Supplier::class . ':insert');
    $group->post('/update', App\Controller\Supplier::class . ':update');
    $group->post('/delete', App\Controller\Supplier::class . ':delete');
    $group->post('/listingdata', App\Controller\Supplier::class . ':listingdata');
});

$app->group('/enterprise', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', App\Controller\Enterprise::class . ':list');
    $group->get('/detalhes/{id}', App\Controller\Enterprise::class . ':details');
    $group->get('/detalhes', App\Controller\Enterprise::class . ':details');
    $group->post('/insert', App\Controller\Enterprise::class . ':insert');
    $group->post('/update', App\Controller\Enterprise::class . ':update');
    $group->post('/delete', App\Controller\Enterprise::class . ':delete');
    $group->post('/listingdata', App\Controller\Enterprise::class . ':listingdata');
});
