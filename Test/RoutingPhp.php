<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/13/15
 * Time: 11:11 PM.
 */

namespace Test;

use Interfaces\TestInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Validator\RouteValidator;

/**
 * Class MainTest.
 */
class RoutingPhp implements TestInterface
{

    public function runTest()
    {
        $locator = new FileLocator(array(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."config"));

        $loader = new PhpFileLoader($locator);

        $routes = $loader->load("routing.php");

        $validator = new RouteValidator(new UrlMatcher($routes, new RequestContext("/")));

        $validator->validate();;
    }
}
