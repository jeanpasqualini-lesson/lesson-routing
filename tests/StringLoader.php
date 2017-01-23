<?php

namespace tests;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;


/**
 * StringLoader
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
class StringLoader
{
    /**
     * getLoaderClassByType
     *
     * @param $type
     *
     * @return string
     * @throws \Exception
     */
    protected function getLoaderClassByType($type)
    {
        switch ($type) {
            case 'yaml':
                return YamlFileLoader::class;
                break;
            case 'xml':
                return XmlFileLoader::class;
                break;
        }

        throw new \Exception('not supported loader '.$type);
    }

    /**
     * load
     *
     * @param $type
     * @param $resource
     *
     * @return RouteCollection
     */
    public function load($type, $resource)
    {
        $loaderClass = $this->getLoaderClassByType($type);

        $loader = new $loaderClass(new FileLocator(sys_get_temp_dir()));

        $configs = explode('-----', $resource);
        foreach ($configs as $id => $config)
        {
            file_put_contents(sys_get_temp_dir().'/routes'.$id.'.'.$type, $config);
        }

        $routeCollection = $loader->load('routes0.'.$type);

        return $routeCollection;
    }
}