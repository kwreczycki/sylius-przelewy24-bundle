<?php

namespace KW\Bundle\SyliusPrzelewy24Bundle\Payum\Przelewy24\Action;

use KW\Bundle\SyliusPrzelewy24Bundle\Payum\Przelewy24\Api;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Notify;
use SM\Factory\FactoryInterface;
use Sylius\Bundle\PayumBundle\Payum\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class NotifyAction extends PaymentAwareAction implements ApiAwareInterface
{
    /** @var RepositoryInterface */
    protected $paymentRepository;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var string */
    protected $identifier;

    /** @var Api */
    protected $api;

    public function __construct(
        ObjectRepository $paymentRepository,
        ObjectManager $objectManager,
        FactoryInterface $factory,
        $identifier
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->objectManager = $objectManager;
        $this->identifier = $identifier;
        $this->factory = $factory;
    }

    public function setApi($api)
    {
        if (!$api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = $request->getModel();

        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->findOneBy([$this->identifier => $model['p24_payment_id']]);

        $model['p24_amount'] = $payment->getAmount();
        $state = $this->api->getPaymentStatus($model);

        $details = array_merge($payment->getDetails(), ['state' => $state]);
        $payment->setDetails($details);

        $status = new GetStatus($payment);
        $this->payment->execute($status);

        $nextState = $status->getValue();

        $this->updatePaymentState($payment, $nextState);

        $this->objectManager->flush();
    }

    private function updatePaymentState($payment, $nextState)
    {
        $stateMachine = $this->factory->get($payment, PaymentTransitions::GRAPH);

        if (null !== $transition = $stateMachine->getTransitionToState($nextState)) {
            $stateMachine->apply($transition);
        }
    }

    public function supports($request)
    {
        return $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess;
    }
}