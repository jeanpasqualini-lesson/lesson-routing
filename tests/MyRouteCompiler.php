<?php

namespace tests;

use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCompilerInterface;


/**
 * MyRouteCompiler
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
class MyRouteCompiler implements RouteCompilerInterface
{
    public static function compile(Route $route)
    {
        return new CompiledRoute(
            $staticPrefix   = '/',
            $regex          = '/my-route/i',
            $tokens         = array(),
            $pathVariables  = array(),
            $hostRegex      = null,
            $hostTokens     = array(),
            $hostVariables  = array(),
            $variables      = array()
        );
    }
}