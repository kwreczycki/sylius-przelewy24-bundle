<?php

namespace KW\Bundle\SyliusPrzelewy24Bundle\DependencyInjection\Factory;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Przelewy24Factory extends AbstractPaymentFactory
{
    const PAYMENT_NAME = 'przelewy24';

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);

        $builder->children()
            ->arrayNode('api')
                ->children()
                    ->booleanNode('sandbox')->end()
                    ->scalarNode('gateway_id')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('crc_key')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('return_url_domain')->isRequired()->cannotBeEmpty()->end()
                ->end()
            ->end()
                ->arrayNode('apis')
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                ->end()
                ->arrayNode('actions')
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                ->end()
            ->end();
    }

    public function createFactoryConfig()
    {
        $config = parent::createFactoryConfig();

        $config['payum.extension.payum.extensions.storage.sylius_component_core_model_payment']
            = new Reference('payum.extension.storage.sylius_component_core_model_payment');

        $config['payum.extension.payum.extension.storage.sylius_component_core_model_order']
            = new Reference('payum.extension.storage.sylius_component_core_model_order');

        return $config;
    }

    public function getName()
    {
        return self::PAYMENT_NAME;
    }
}
