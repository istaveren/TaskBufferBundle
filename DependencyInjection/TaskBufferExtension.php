<?php

namespace Smentek\TaskBufferBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TaskBufferExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('task_buffer.xml');
    }

    /**
	* Returns the base path for the XSD files.
	*
	* @return string The XSD base path
	*/
    public function getXsdValidationBasePath()
    {
        return null;
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/symfony';
    }

    public function getAlias()
    {
        return 'task_buffer';
    }

}