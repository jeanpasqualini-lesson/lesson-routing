<?php

namespace tests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * RouteDeclareTest
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
class RouteDeclareTest extends \PHPUnit_Framework_TestCase
{
    use RoutingLoaderTrait;

    // 'resource', 'type', 'prefix', 'path', 'host', 'schemes', 'methods', 'defaults', 'requirements', 'options', 'condition'

    public function testSimpleRoute()
    {
        ?>
        simple_route:
            path: /simple-path
            defaults: { _controller: Fixture\SimpleRoute\Controller::mainAction }

        <?php $this->registerYaml(); ?>

        <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="simple_route" path="/simple-path">
                <default key="_controller">Fixture\SimpleRoute\Controller::mainAction</default>
            </route>

        </routes>

        <?php $this->registerXml(); ?>

        <?php
        $this->registerClosure(function(RouteCollection $routeCollection)
        {
            $routeCollection->add('simple_route', new Route(
                $path           = '/simple-path',
                $defaults       = array(
                    '_controller' => 'Fixture\SimpleRoute\Controller::mainAction'
                )
            ));
        });

        ?>
        namespace Fixture\SimpleRoute {

            use Symfony\Component\Routing\Annotation\Route;

            class Controller {

                /**
                * @Route("/simple-path", name="simple_route")
                */
                public function mainAction() { }

            }
        }
        <?php

        $this->registerAnnotation('Fixture\SimpleRoute\Controller');

        $this->load();
    }

    public function testHost()
    {
        ?>
        host_route:
            path: /host-route
            defaults: { _controller: Fixture\Host\Controller::mainAction }
            host: www.mami.com
        <?php
        $this->registerYaml();

        ?>
        <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">
            <route id="host_route" path="/host-route" host="www.mami.com">
                <default key="_controller">Fixture\Host\Controller::mainAction</default>
            </route>
        </routes>
        <?php
        $this->registerXml();

        $this->registerClosure(function(RouteCollection $routeCollection)
        {
           $routeCollection->add('host_route', new Route(
               $path            = '/host-route',
               $params          = array(
                   '_controller' => 'Fixture\Host\Controller::mainAction'
               ),
               $requirements    = array(),
               $options         = array(),
               $host            = 'www.mami.com'
           ));
        });

        ?>
        namespace Fixture\Host {

            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

            class Controller {
                /**
                * @Route("/host-route", name="host_route", host="www.mami.com")
                */
                public function mainAction() {}
            }
        }
        <?php
        $this->registerAnnotation('Fixture\Host\Controller');

        $this->load();
    }

    public function testAutoNamedRouteAnnotation()
    {
        ?>
        namespace Fixture\AutoNamed {

            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

            class Controller {
                /**
                * @Route("/auto-named")
                */
                public function mainAction() {}
            }
        }
        <?php

        $this->registerAnnotation('Fixture\AutoNamed\Controller');

        $this->load();

        $this->assertEquals(array('fixture_autonamed_main'), array_keys($this->routeCollection->all()));
    }

    public function testCondition()
    {
        ?>
        route_condition:
            path: /route-condition
            condition: request.isMethod('POST')
            defaults: { _controller: 'Fixture\Condition\Controller::mainAction' }
        <?php
        $this->registerYaml();

        ?>
        <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">
            <route id="route_condition" path="/route-condition">
                <condition>request.isMethod('POST')</condition>
                <default key="_controller">Fixture\Condition\Controller::mainAction</default>
            </route>
        </routes>
        <?php
        $this->registerXml();

        $this->registerClosure(function(RouteCollection $routeCollection)
        {
            $routeCollection->add('route_condition', new Route(
                $path = '/route-condition',
                $defaults = array(
                    '_controller' => 'Fixture\\Condition\\Controller::mainAction'
                ),
                $requirements = array(),
                $options = array(),
                $host = '',
                $schemes = array(),
                $methods = array(),
                $condition = "request.isMethod('POST')"
            ));
        });

        $this->load();

        $this->assertException(ResourceNotFoundException::class, function() {
            $this->routerMatcher->matchRequest(Request::create($path='/route-condition', $method='GET'));
        });

        $this->assertTrue((bool) $this->routerMatcher->matchRequest(Request::create($path='/route-condition', $method='POST')));
    }

    public function testRequirements()
    {
        ?>
        route_requirements:
            path: /route-requirements/{integer}
            defaults:
                _controller: Fixture\Requirements\Controller::mainAction
            requirements:
                integer: '\d+'
        <?php
        $this->registerYaml();

        ?>
        <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">
            <route id="route_requirements" path="/route-requirements/{integer}">
                <default key="_controller">Fixture\Requirements\Controller::mainAction</default>
                <requirement key="integer">\d+</requirement>
            </route>
        </routes>
        <?php
        $this->registerXml();

        $this->registerClosure(function(RouteCollection $routeCollection)
        {
            $routeCollection->add('route_requirements', new Route(
                    $path = '/route-requirements/{integer}',
                    $defaults = array(
                        '_controller' => 'Fixture\Requirements\Controller::mainAction'
                    ),
                    $requirements = array(
                        'integer' => '\d+'
                    ),
                    $options = array(),
                    $host = '',
                    $scheme = array(),
                    $methods = array(),
                    $condition = ''
            ));
        });

        ?>
        namespace Fixture\Requirements {

            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

            class Controller {
                /**
                * @Route("/route-requirements/{integer}", name="route_requirements", requirements={"integer":"\d+"})
                */
                public function mainAction() {}
            }
        }
        <?php

        $this->registerAnnotation('Fixture\Requirements\Controller');

        $this->load();

        $this->assertException(ResourceNotFoundException::class, function() {
            $this->routerMatcher->matchRequest(Request::create($path='/route-requirements/A'));
        });

        $this->assertTrue((bool) $this->routerMatcher->matchRequest(Request::create($path='/route-requirements/5')));
    }



    public function testMethod()
    {
        ?>
        route_method:
            path: /route-method
            defaults:
                _controller: Fixture\Method\Controller::mainAction
            methods: [PUT, POST]
        <?php
        $this->registerYaml();

        ?>
        <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">
            <route id="route_method" path="/route-method" methods="PUT|POST">
                <default key="_controller">Fixture\Method\Controller::mainAction</default>
            </route>
        </routes>
        <?php
        $this->registerXml();

        $this->registerClosure(function(RouteCollection $routeCollection)
        {
            $routeCollection->add('route_method', new Route(
                $path = '/route-method',
                $defaults = array(
                    '_controller' => 'Fixture\Method\Controller::mainAction'
                ),
                $requirements = array(),
                $options = array(),
                $host = '',
                $scheme = array(),
                $methods = array('PUT', 'POST'),
                $condition = ''
            ));
        });

        ?>
        namespace Fixture\Method {

            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

            class Controller {
                /**
                * @Route("/route-method", name="route_method")
                * @Method({"POST","PUT"})
                */
                public function mainAction() {}
            }
        }
        <?php

        //$this->registerAnnotation('Fixture\Method\Controller');

        $this->load();
    }

    public function testDefaults()
    {
        ?>
        route_defaults:
            path: /route-defaults
            defaults: { color: red, _controller: Fixture\Defaults\Controller::mainAction }
        <?php
        $this->registerYaml();

        ?>
        <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">
            <route id="route_defaults" path="/route-defaults">
                <default key="color">red</default>
                <default key="_controller">Fixture\Defaults\Controller::mainAction</default>
            </route>
        </routes>
        <?php
        $this->registerXml();

        $this->registerClosure(function(RouteCollection $routeCollection)
        {
            $routeCollection->add('route_defaults', new Route(
                $path = '/route-defaults',
                $defaults = array(
                    'color' => 'red',
                    '_controller' => 'Fixture\Defaults\Controller::mainAction',
                ),
                $requirements = array(),
                $options = array(),
                $host = '',
                $scheme = array(),
                $methods = array(),
                $condition = ''
            ));
        });

        ?>
        namespace Fixture\Defaults {

            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

            class Controller {
                /**
                * @Route("/route-defaults", name="route_defaults", defaults={"color" : "red"})
                */
                public function mainAction() {}
            }
        }
        <?php

        $this->registerAnnotation('Fixture\Defaults\Controller');

        $this->load();
    }

    public function testOptions()
    {
        ?>
        route_options:
            path: /route-options
            defaults: { _controller: Fixture\Options\Controller::mainAction }
            options: { }
        <?php
        $this->registerYaml();

        ?>
        <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">
            <route id="route_options" path="/route-options">
                <default key="_controller">Fixture\Options\Controller::mainAction</default>
            </route>
        </routes>
        <?php
        $this->registerXml();

        $this->registerClosure(function(RouteCollection $routeCollection)
        {
            $routeCollection->add('route_options', new Route(
                $path = '/route-options',
                $defaults = array(
                    '_controller' => 'Fixture\Options\Controller::mainAction'
                ),
                $requirements = array(),
                $options = array(),
                $host = '',
                $scheme = array(),
                $methods = array(),
                $condition = ''
            ));
        });

        ?>
        namespace Fixture\Options {

            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

            class Controller {
                /**
                * @Route("/route-options", name="route_options", options={})
                */
                public function mainAction() {}
            }
        }
        <?php

        $this->registerAnnotation('Fixture\Options\Controller');

        $this->load();
    }

    public function testSchemes()
    {
        ?>
        route_schemes:
            path: /route-schemes
            defaults: { _controller: 'Fixture\Schemes\Controller::mainAction' }
            schemes: ['https']
        <?php
        $this->registerYaml();

        ?>
        <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">
            <route id="route_schemes" path="/route-schemes" schemes="https">
                <default key="_controller">Fixture\Schemes\Controller::mainAction</default>
            </route>
        </routes>
        <?php
        $this->registerXml();

        $this->registerClosure(function(RouteCollection $routeCollection)
        {
            $routeCollection->add('route_schemes', new Route(
                $path = '/route-schemes',
                $defaults = array(
                    '_controller' => 'Fixture\Schemes\Controller::mainAction'
                ),
                $requirements = array(),
                $options = array(),
                $host = '',
                $scheme = array('https'),
                $methods = array(),
                $condition = ''
            ));
        });

        ?>
        namespace Fixture\Schemes {

            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

            class Controller {
                /**
                * @Route("/route-schemes", name="route_schemes", schemes="https")
                */
                public function mainAction() {}
            }
        }
        <?php

        $this->registerAnnotation('Fixture\Schemes\Controller');

        $this->load();

        $this->assertException(ResourceNotFoundException::class, function() {
            $this->matchRequest(Request::create($path='http://www.mami.com/route-schemes'));
        });

        $this->assertTrue((bool) $this->matchRequest(
            Request::create('https://www.mami.com/route-schemes')
        ));

        $this->assertEquals('https://localhost/route-schemes', $this->urlGenerator->generate('route_schemes'));
    }

    public function testPrefix()
    {
        ?>
        main:
            resource: routes1.yaml
            prefix: /main
        -----
        home:
            path: /home
        <?php

        $this->registerYaml();

        ?>
        <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">
            <import resource="routes1.xml" prefix="/main"/>
        </routes>
        -----
        <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">
            <route id="home" path="/home">
            </route>
        </routes>
        <?php
        $this->registerXml();

        $this->registerClosure(function(RouteCollection $routeCollection)
        {
            $mainCollection = new RouteCollection();
            $mainCollection->add('home', new Route(
                $path = '/home'
            ));
            $mainCollection->addPrefix('/main');

            $routeCollection->addCollection($mainCollection);
        });

        $this->load();

        $this->assertTrue((bool) $this->matchRequest(
            Request::create('/main/home')
        ));
    }

}