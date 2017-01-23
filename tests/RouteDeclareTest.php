<?php

namespace tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\ClosureLoader;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

/**
 * RouteDeclareTest
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
class RouteDeclareTest extends \PHPUnit_Framework_TestCase
{
    // 'resource', 'type', 'prefix', 'path', 'host', 'schemes', 'methods', 'defaults', 'requirements', 'options', 'condition'

    /** @var RouteCollection */
    protected $routeCollection;
    protected $routerMatcherDumped;
    protected $routerGeneratorDumped;
    /** @var UrlMatcher */
    protected $routerMatcher;

    protected $current = array();

    public function setUp()
    {
        $this->current = array();
        $this->routeCollection = null;
        ob_start();
    }

    public function getContentOb()
    {
        $content = ob_get_clean();
        $content = implode(
            PHP_EOL,
            array_filter(
                array_map(
                    function($item) { return substr($item, 8); },
                    explode(PHP_EOL, $content)
                ),
                function($item) { return !empty($item); }
            )
        );
        return $content;
    }

    public function registerYaml()
    {
        $this->current['yaml'] = $this->getContentOb();
        ob_start();
    }

    public function registerXml()
    {
        $this->current['xml'] = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.$this->getContentOb();
        ob_start();
    }

    public function registerAnnotation($class)
    {
        file_put_contents(sys_get_temp_dir().'/controller.php', '<?php'.PHP_EOL.$this->getContentOb());
        require sys_get_temp_dir().'/controller.php';

        $this->current['annotation'] = $class;
        ob_start();
    }

    public function registerClosure($closure)
    {
        $this->current['closure'] = function() use ($closure) {

            $routeCollection = new RouteCollection();
            $closure($routeCollection);

            return $routeCollection;
        };
    }

    /**
     * @param array $configs
     */
    public function load()
    {
        ob_end_clean();

        $configs = $this->current;

        $previousRouteCollection = null;

        $previousLoader = null;
        $previousRouterMatcherDumped = null;
        $routerMatcherDumped = null;
        $routeCollection = null;

        foreach ($configs as $type => $config) {
            switch ($type)
            {
                case 'yaml':
                    file_put_contents(sys_get_temp_dir().'/routes.yml', $config);
                    $loader = new YamlFileLoader(new FileLocator(sys_get_temp_dir()));
                    $routeCollection = $loader->load('routes.yml');
                    $routerMatcherDumper = new PhpMatcherDumper($routeCollection);
                    $routerMatcherDumped = $routerMatcherDumper->dump();
                    break;
                case 'xml':
                    file_put_contents(sys_get_temp_dir().'/routes.xml', $config);
                    $loader = new XmlFileLoader(new FileLocator(sys_get_temp_dir()));
                    $routeCollection = $loader->load('routes.xml');
                    $routerMatcherDumper = new PhpMatcherDumper($routeCollection);
                    $routerMatcherDumped = $routerMatcherDumper->dump();
                    break;
                case 'closure':
                    $loader = new ClosureLoader();
                    $routeCollection = $loader->load($config);
                    $routerMatcherDumper = new PhpMatcherDumper($routeCollection);
                    $routerMatcherDumped = $routerMatcherDumper->dump();
                    break;
                case 'annotation':
                    $loader = new AnnotatedRouteControllerLoader(new AnnotationReader());
                    $routeCollection = $loader->load($config);
                    $routerMatcherDumper = new PhpMatcherDumper($routeCollection);
                    $routerMatcherDumped = $routerMatcherDumper->dump();
                    break;

            }

            if ($previousRouterMatcherDumped !== null && $routerMatcherDumped !== null) {
                $this->assertEquals(
                    $previousRouterMatcherDumped,
                    $routerMatcherDumped,
                    $previousLoader. ' vs '.$type
                );
            }

            $previousLoader = $type;
            $previousRouterMatcherDumped = $routerMatcherDumped;
        }

        $this->routeCollection = $routeCollection;
        $this->routerMatcher = new UrlMatcher($routeCollection, new RequestContext());
    }

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

    public function assertException($exceptionClass, $callback)
    {
        try {
            $callback();
        } catch(\Exception $e) {
            $this->assertEquals($exceptionClass, get_class($e), 'asserting exception');
            return;
        }

        $this->assertEquals($exceptionClass, '---------', 'asserting exception');
    }

    public function testCondition()
    {
        ?>
        route_condition:
            path: /route-condition
            condition: request.isMethod('POST')
        <?php
        $this->registerYaml();

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
            path: /route-requirements/{int}
            requirements:
                int: '\d+'
        <?php
        $this->registerYaml();

        $this->assertException(ResourceNotFoundException::class, function() {
            $this->routerMatcher->matchRequest(Request::create($path='/route-requirements'));
        });

        $this->assertTrue((bool) $this->routerMatcher->matchRequest(Request::create($path='/route-requirements')));

        $this->load();
    }



    public function testMethod()
    {
        ?>
        route_method:
        path: /route-method
        methods: PUT|POST
        <?php
        $this->registerYaml();

        $this->load();
    }

    public function testDefaults()
    {
        ?>
        route_defaults:
        path: /route-defaults
        defaults: { color: red }
        <?php
        $this->registerYaml();

        $this->load();
    }
}