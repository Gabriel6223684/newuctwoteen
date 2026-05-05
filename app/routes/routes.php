<?php

declare(strict_types=1);

$app->get('/', app\controller\Home::class . ':home');
$app->get('/home', app\controller\Home::class . ':home');

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
