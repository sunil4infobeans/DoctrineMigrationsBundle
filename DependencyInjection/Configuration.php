<?php


namespace Doctrine\Bundle\MigrationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * DoctrineMigrationsExtension configuration structure.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder The config tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('doctrine_migrations');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('doctrine_migrations', 'array');
        }

        $rootNode
            ->beforeNormalization()
                ->ifTrue(function($v) {
                    if(empty($v)) {
                        return true;
                    }
                    $firstConfigValue = array_shift($v);

                    return !is_array($firstConfigValue);
                })
                ->then(function($v) {
                    $v = array('default_entity_manager' => $v);

                    return $v;
                })
            ->end()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
                ->scalarNode('dir_name')->defaultValue('%kernel.root_dir%/DoctrineMigrations')->cannotBeEmpty()->end()
                ->scalarNode('namespace')->defaultValue('Application\Migrations')->cannotBeEmpty()->end()
                ->scalarNode('table_name')->defaultValue('migration_versions')->cannotBeEmpty()->end()
                ->scalarNode('name')->defaultValue('Application Migrations')->end()
            ->end()
        ;

        return $treeBuilder;
    }


    /**
     * Find organize migrations modes for their names
     *
     * @return array
     */
    private function getOrganizeMigrationsModes()
    {
        $constPrefix = 'VERSIONS_ORGANIZATION_';
        $prefixLen = strlen($constPrefix);
        $refClass = new \ReflectionClass('Doctrine\DBAL\Migrations\Configuration\Configuration');
        $constsArray = $refClass->getConstants();
        $namesArray = array();

        foreach ($constsArray as $key => $value) {
            if (strpos($key, $constPrefix) === 0) {
                $namesArray[] = substr($key, $prefixLen);
            }
        }

        return $namesArray;
    }
}
