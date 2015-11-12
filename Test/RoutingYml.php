<?php
/**
 * Created by PhpStorm.
 * User: prestataire
 * Date: 12/11/15
 * Time: 16:46
 */

namespace Test;


use Interfaces\TestInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Validator\RouteValidator;

class RoutingYml implements TestInterface
{
    public function runTest()
    {
        $locator = new FileLocator(array(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."config"));

        $loader = new YamlFileLoader($locator);

        $routes = $loader->load("routing.yml");

        $validator = new RouteValidator(new UrlMatcher($routes, new RequestContext("/")));

        $validator->validate();;
    }

}