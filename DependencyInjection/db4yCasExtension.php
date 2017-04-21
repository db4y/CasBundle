<?php

namespace db4y\CasBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class db4yCasExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $def = $container->getDefinition('db4y_cas.cas_authenticator');
        $def->replaceArgument(0, [
            'host' => $config['cas']['host'],
            'port' => $config['cas']['port'],
            'context' => $config['cas']['context'],
        ]);
        $def->replaceArgument(2, $config['restricted']);

        $container->setParameter('db4y_cas.restricted', $config['restricted']);
    }
}
