<?php

namespace App\Controllers\Auth;

use Cartalyst\Sentinel\Sentinel as Auth;
use App\Controllers\Controller;
use Slim\Http\Request;
use Slim\Http\Response;

class LogoutController extends Controller
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function logout(Request $request, Response $response)
    {
        $this->auth->logout(null, true);

        return $response->withRedirect('/');
    }
}
