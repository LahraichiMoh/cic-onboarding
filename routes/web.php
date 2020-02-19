<?php
/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
| This is the application global routes.
|
*/
$app->group('', function () {
    // Home - Dashboard
    $this->get('/', App\Controllers\HomeController::class . ':index')->setName('home');
    $this->post('/', App\Controllers\HomeController::class . ':newAction');

})->add(new App\Middlewares\Authenticated(
    $container->get(Cartalyst\Sentinel\Sentinel::class),
    $container->get('router')
));

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
|
| This is the core routes of the application authentication.
|
*/
$app->group('/auth', function () {
    $this->get('/login', App\Controllers\Auth\LoginController::class . ':index')->setName('auth.login');
    $this->post('/login', App\Controllers\Auth\LoginController::class . ':login');
    $this->get('/register', App\Controllers\Auth\RegisterController::class . ':index')->setName('auth.register');
    $this->post('/register', App\Controllers\Auth\RegisterController::class . ':register');

    // Dummy data API GET - for register
    $this->get('/cities-region', App\Controllers\Auth\RegisterController::class . ':cities');
    $this->get('/regions', App\Controllers\Auth\RegisterController::class . ':regions');
    $this->get('/sectors', App\Controllers\Auth\RegisterController::class . ':sectors');
    $this->get('/branches', App\Controllers\Auth\RegisterController::class . ':branches');
    $this->get('/sub-branches', App\Controllers\Auth\RegisterController::class . ':subBranches');
    
    // Send step
    $this->post('/send-step', App\Controllers\Auth\RegisterController::class . ':sendStep');

})->add(new App\Middlewares\Guest($container->get(Cartalyst\Sentinel\Sentinel::class)));

/*
|--------------------------------------------------------------------------
| Verification phone number and email
|--------------------------------------------------------------------------
|
*/
$app->group('', function () {
    $this->post('/verification/phone-number', App\Controllers\VerificationController::class . ':phoneNumber');
    $this->post('/verification/check-phone-number', App\Controllers\VerificationController::class . ':checkPhoneNumber');
    $this->post('/verification/email', App\Controllers\VerificationController::class . ':email');
    $this->post('/verification/check-email', App\Controllers\VerificationController::class . ':checkEmail');

})->add(new App\Middlewares\Guest($container->get(Cartalyst\Sentinel\Sentinel::class)));


// Test route
$app->group('', function () {
    $this->get('/test', App\Controllers\HomeController::class . ':test')->setName('test');
});