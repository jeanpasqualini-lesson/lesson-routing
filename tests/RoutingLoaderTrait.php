<?php

namespace tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\ClosureLoader;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * RoutingLoaderTrait
 *
 * @author Jean Pasqualini <jean.pasqualini@digitaslbi.fr>
 * @copyright 2016 DigitasLbi France
 * @package tests;
 */
trait RoutingLoaderTrait
{

    /** @var RouteCollection */
    protected $routeCollection;
    protected $routerMatcherDumped;
    protected $routerGeneratorDumped;
    /** @var UrlMatcher */
    protected $routerMatcher;
    /** @var UrlGenerator */
    protected $urlGenerator;

    protected $current = array();

    public function setUp()
    {
        $this->current = array();
        $this->routeCollection = null;
        $this->routerMatcherDumped;
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
        $this->current['xml'] = str_replace(
            '-----',
            '-----'.'<?xml version="1.0" encoding="UTF-8"?>',
            $this->current['xml']
        );
        //var_dump($this->current['xml']);
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

    public function matchRequest(Request $request)
    {
        $context = new RequestContext();
        $context->fromRequest($request);
        $this->routerMatcher->setContext($context);
        return $this->routerMatcher->matchRequest($request);
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

        $dumpOptions = array(
            'base_class' => RedirectableUrlMatcher::class
        );

        foreach ($configs as $type => $config) {
            switch ($type)
            {
                case 'yaml':
                case 'xml':
                    $loader = new StringLoader();
                    $routeCollection = $loader->load($type, $config);
                    $routerMatcherDumper = new PhpMatcherDumper($routeCollection);
                    $routerMatcherDumped = $routerMatcherDumper->dump($dumpOptions);
                    break;
                case 'closure':
                    $loader = new ClosureLoader();
                    $routeCollection = $loader->load($config);
                    $routerMatcherDumper = new PhpMatcherDumper($routeCollection);
                    $routerMatcherDumped = $routerMatcherDumper->dump($dumpOptions);
                    break;
                case 'annotation':
                    $loader = new AnnotatedRouteControllerLoader(new AnnotationReader());
                    $routeCollection = $loader->load($config);
                    $routerMatcherDumper = new PhpMatcherDumper($routeCollection);
                    $routerMatcherDumped = $routerMatcherDumper->dump($dumpOptions);
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
        $this->urlGenerator = new UrlGenerator($routeCollection, new RequestContext());
        $this->routerMatcherDumped = $previousRouterMatcherDumped;
    }
}