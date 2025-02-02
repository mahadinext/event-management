<?php
// require_once(__DIR__ . '/../app/core/Router.php');
// require_once (__DIR__ . '/../app/controllers/auth/AuthController.php');
// require_once (__DIR__ . '/../app/controllers/dashboard/DashboardController.php');

$router->get('/', 'EventDashboardController@index');
$router->post('/events/register', 'EventDashboardController@register');

$router->group(['prefix' => '/admin', 'namespace' => 'Admin'], function($router) {
    $router->get('/', 'DashboardController@index');
    $router->get('/dashboard', 'DashboardController@index');
    $router->get('/login', 'AuthController@showLoginForm');
    $router->post('/login', 'AuthController@login');
    $router->get('/register', 'AuthController@showRegistrationForm');
    $router->post('/register', 'AuthController@register');
    $router->get('/logout', 'AuthController@logout');

    $router->get('/events', 'EventController@index');
    $router->get('/events/create', 'EventController@create');
    $router->post('/events/store', 'EventController@store');
    $router->get('/events/edit/{id}', 'EventController@edit');
    $router->put('/events/update/{id}', 'EventController@update');
    $router->delete('/events/delete/{id}', 'EventController@delete');
    $router->post('/events/{id}/restore', 'EventController@restore');

    // Event Attendees Routes
    $router->get('/event-attendees', 'EventAttendeeController@index');
    $router->get('/event-attendees/export-csv', 'EventAttendeeController@exportCsv');
});

$router->group(['prefix' => '/attendee'], function($router) {
    $router->get('/login', 'AuthController@showLoginForm');
    $router->post('/login', 'AuthController@login');
    $router->get('/register', 'AuthController@showRegistrationForm');
    $router->post('/register', 'AuthController@register');
    $router->get('/logout', 'AuthController@logout');
});

$router->group(['prefix' => '/api'], function($router) {
    $router->get('/events', 'EventsController@index');
    $router->get('/events/{id}', 'EventsController@show');
});

// 404 handler
$router->notFound(function() {
    header("HTTP/1.0 404 Not Found");
    require_once 'views/errors/404.php';
});
