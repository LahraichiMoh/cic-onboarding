<?php

namespace App\Controllers\Auth;

use App\Controllers\Controller;
use Cartalyst\Sentinel\Sentinel as Auth;
use App\Session\Flash;
use App\Views\View;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\User;

class LoginController extends Controller
{
    protected $auth;

    protected $view;

    protected $flash;

    public function __construct(View $view, Flash $flash, Auth $auth)
    {
        $this->view = $view;
        $this->flash = $flash;
        $this->auth = $auth;
    }

    public function index(Request $request, Response $response)
    {
        return $this->view->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response)
    {
        //dump($request->getParsedBody());
        $response->withRedirect('/register');

        // $data = $this->validate($request, [
        //     'email' => ['required', 'email'],
        //     'password' => ['required'],
        // ]);
        // $array = [];
        // $attempt = $this->auth->Authenticate([
        //     'email' => $data['email'],
        //     'password' => $data['password']
        // ], isset($data['remember']));

        // if (!$attempt) {
        //     $path = $response->withRedirect($request->getUri()->getPath());
        //     $error = "L'email ou le mot de passe est incorrecte, réessayer";
        //     $array['path'] = $path;
        //     $array['error'] = $error;

        //     return $response->withStatus(200)
        //     ->withHeader('Content-Type', 'application/json')
        //     ->write(json_encode($array));
        //     // $this->flash->now('error', 'Could not sign you in with those details.');
        //     // return $response->withRedirect($request->getUri()->getPath());
        // }   
        //     $_SESSION['email'] = $data['email'];
        //     $path = $response->withRedirect('/profile');
        //     $success = 'Authentification Reussie';
        //     $array['path'] = $path;
        //     $array['success'] = $success;
        
        //     return $response->withStatus(200)
        //     ->withHeader('Content-Type', 'application/json')
        //     ->write(json_encode($array));
    }
}
