<?php

namespace KW\Bundle\SyliusPrzelewy24Bundle;

use KW\Bundle\SyliusPrzelewy24Bundle\DependencyInjection\Factory\Przelewy24Factory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SyliusPrzelewy24Bundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        /** @var PayumExtension $payum */
        $payum = $container->getExtension('payum');
        $payum->addPaymentFactory(new Przelewy24Factory());
    }
}
