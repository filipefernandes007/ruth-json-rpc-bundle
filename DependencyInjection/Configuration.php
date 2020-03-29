<?php

namespace Ruth\RpcBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder $builder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('ruth_rpc');

        return $builder;
    }
}
