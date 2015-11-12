<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$routes = new RouteCollection();

$routes->add("route_name", new Route("/foo", array("controller" => "MyController")));

$routes->add("other_route", new Route(
    "/archive/{month}", // Path
    array("controller" => "showArchive"), // Defaults Valeurs
    array("month" => "[0-9]{4}-[0-9]{2}", "subdomain" => "www|n"), // Requrirements
    array(), // Options
    "",//"{subdomain}.example.com", //Host
    array(), // Schemes,
    array() // Methods
));

return $routes;