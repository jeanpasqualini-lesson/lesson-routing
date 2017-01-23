<?php
namespace Validator;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;

/**
 * Created by PhpStorm.
 * User: prestataire
 * Date: 12/11/15
 * Time: 16:44
 */
class RouteValidator
{
    protected $urlMatcher;

    public function __construct(UrlMatcher $urlMatcher)
    {
        $this->urlMatcher = $urlMatcher;
    }

    public function validate()
    {
        $matcher = $this->urlMatcher;

        $parameters = $matcher->match("/foo");

        echo print_r($parameters, true);

        $parameters = $matcher->match("/archive/2012-01");

        echo print_r($parameters, true);

        try
        {
            $matcher->match("/archive/foo");
        }
        catch(ResourceNotFoundException $e)
        {
            echo "[RESOURCE NOT FOUND] ".$e->getMessage().PHP_EOL;
        }
    }
}