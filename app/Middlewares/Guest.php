<?php

namespace App\Middlewares;

use Cartalyst\Sentinel\Sentinel as Auth;
use Slim\Http\Request;
use Slim\Http\Response;

class Guest
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        if (!$this->auth->guest()) {
            return $response->withRedirect('/');
        }

        return $next($request, $response);
    }
}
