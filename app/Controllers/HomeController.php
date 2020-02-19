<?php

namespace App\Controllers;

use App\Views\View;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\User;
class HomeController extends Controller
{
    protected $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function index(Request $request, Response $response)
    {
        // $email = $_SESSION['email'];
        // $user = User::where('email', $email)->first();
        // return $this->view->render($response, 'templates/index.twig', array('user' => $user));
        return $this->view->render($response, 'layout.twig');
    }

    public function test(Request $request, Response $response)
    {
        $name = 'Ulrich Grah';
        $code = '0000';
        return $this->view->render($response, 'emails/verification-code.twig', array('name' => $name, 'code' => $code));
    }
   
}
