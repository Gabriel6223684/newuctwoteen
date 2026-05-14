<?php

declare(strict_types=1);
/** @var \Slim\App $app */

$app->get('/', app\controller\Home::class . ':home')->add(app\middleware\Middleware::web());
$app->get('/home', app\controller\Home::class . ':home')->add(app\middleware\Middleware::web());
$app->get('/login', app\controller\Login::class . ':login')->add(app\middleware\Middleware::web());

$app->group('/authentication', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->post('/google', app\controller\Login::class . ':google');
    $group->post('/auth', app\controller\Login::class . ':authenticate');
    $group->post('/preregister', app\controller\Login::class . ':preRegister');
});

$app->group('/pais', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', app\controller\Country::class . ':list');
    $group->get('/detalhes/{id}', app\controller\Country::class . ':details');
    $group->get('/detalhes', app\controller\Country::class . ':details');
    $group->post('/insert', app\controller\Country::class . ':insert');
    $group->post('/update', app\controller\Country::class . ':update');
    $group->post('/delete', app\controller\Country::class . ':delete');
    $group->post('/listingdata', app\controller\Country::class . ':listingdata');
});

$app->group('/usuario', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', app\controller\User::class . ':list');
    $group->get('/detalhes/{id}', app\controller\User::class . ':details');
    $group->get('/detalhes', app\controller\User::class . ':details');
    $group->post('/insert', app\controller\User::class . ':insert');
    $group->post('/update', app\controller\User::class . ':update');
    $group->post('/delete', app\controller\User::class . ':delete');
    $group->post('/listingdata', app\controller\User::class . ':listingdata');
});


$app->group('/cliente', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', app\controller\Customer::class . ':list');
    $group->get('/detalhes/{id}', app\controller\Customer::class . ':details');
    $group->get('/detalhes', app\controller\Customer::class . ':details');
    $group->post('/insert', app\controller\Customer::class . ':insert');
    $group->post('/update', app\controller\Customer::class . ':update');
    $group->post('/delete', app\controller\Customer::class . ':delete');
    $group->post('/listingdata', app\controller\Customer::class . ':listingdata');
});

$app->group('/fornecedor', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', app\controller\Supplier::class . ':list');
    $group->get('/detalhes/{id}', app\controller\Supplier::class . ':details');
    $group->get('/detalhes', app\controller\Supplier::class . ':details');
    $group->post('/insert', app\controller\Supplier::class . ':insert');
    $group->post('/update', app\controller\Supplier::class . ':update');
    $group->post('/delete', app\controller\Supplier::class . ':delete');
    $group->post('/listingdata', app\controller\Supplier::class . ':listingdata');
});

$app->group('/enterprise', function (Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/lista', app\controller\Enterprise::class . ':list');
    $group->get('/detalhes/{id}', app\controller\Enterprise::class . ':details');
    $group->get('/detalhes', app\controller\Enterprise::class . ':details');
    $group->post('/insert', app\controller\Enterprise::class . ':insert');
    $group->post('/update', app\controller\Enterprise::class . ':update');
    $group->post('/delete', app\controller\Enterprise::class . ':delete');
    $group->post('/listingdata', app\controller\Enterprise::class . ':listingdata');
});
