<?php
/**
 * Created by PhpStorm.
 * User: prestataire
 * Date: 12/11/15
 * Time: 17:07
 */

namespace Test;

use Interfaces\TestInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Validator\RouteValidator;

class RoutingXml implements TestInterface
{
    public function runTest()
    {
        $locator = new FileLocator(array(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."config"));

        $loader = new XmlFileLoader($locator);

        $routes = $loader->load("routing.xml");

        $validator = new RouteValidator(new UrlMatcher($routes, new RequestContext("/")));

        $validator->validate();
    }

}