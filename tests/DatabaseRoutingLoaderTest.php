<?php
namespace tests;

use PDO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

/**
 * DatabaseRoutingLoaderTest
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
class DatabaseRoutingLoaderTest extends \PHPUnit_Framework_TestCase
{
    use RoutingLoaderTrait;

    /** @var PDO */
    protected $pdo;

    protected $databaseRoutingLoader;

    /**
     * @return string
     */
    protected function getDatabaseFile()
    {
        $directory = sys_get_temp_dir().'/phpunit-lesson-routing';
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        return $directory.'/database_loader_test.sqlite';
    }

    public function setUp()
    {
        if (!extension_loaded('pdo_sqlite')) {
            return;
        }
        $file = $this->getDatabaseFile();
        $this->pdo = new PDO('sqlite:'.$file);
        $this->pdo->query('CREATE TABLE routes (name VARCHAR(255), path VARCHAR(255));');
        $this->pdo->query('INSERT INTO routes VALUES("home", "/home");');
        $this->databaseRoutingLoader = new DatabaseRoutingLoader($this->pdo);
    }

    public function tearDown()
    {
        if (null !== $this->pdo) {
            $this->pdo->query('DROP TABLE routes;');
            $this->pdo = null;
        }
    }

    public function testLoad()
    {
        $routeCollection = $this->databaseRoutingLoader->load('all');

        $this->routerMatcher = new UrlMatcher($routeCollection, new RequestContext());

        $this->assertException(ResourceNotFoundException::class, function() {
            $this->matchRequest(Request::create('/unknow'));
        });

        $this->assertTrue((boolean) $this->matchRequest(Request::create('/home')));
    }
}