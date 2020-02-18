<?php

namespace App\Providers;

use Slim\Http\Request;
use App\Security\Csrf;
use App\Session\Flash;
use App\Views\View;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Cartalyst\Sentinel\Sentinel as Auth;

class ViewShareServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    public function boot()
    {
        $container = $this->getContainer();
        
        $container->get(View::class)->share([
            'config'  => $container->get('config'),
            'request' => $container->get(Request::class),
            'auth'    => $container->get(Auth::class),
            'csrf'    => $container->get(Csrf::class),
            'flash'   => $container->get(Flash::class),
        ]);
    }

    public function register()
    {
        //
    }
}
