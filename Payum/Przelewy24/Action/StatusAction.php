<?php

namespace KW\Bundle\SyliusPrzelewy24Bundle\Payum\Przelewy24\Action;

use KW\Bundle\SyliusPrzelewy24Bundle\Payum\Przelewy24\Api;
use Doctrine\Common\Persistence\ObjectRepository;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;;
use Payum\Core\Security\TokenInterface;

class StatusAction extends PaymentAwareAction
{
    private $paymentRepo;

    public function __construct(ObjectRepository $paymentRepo)
    {
        $this->paymentRepo = $paymentRepo;
    }

    public function execute($request)
    {
        /** @var PaymentInterface $payment */
        $model = $request->getModel();

        if ($model instanceof PaymentInterface) {
            $details = ArrayObject::ensureArrayObject($model->getDetails());

            if ($details['state'] == Api::STATUS_SUCCESS) {
                $request->markCaptured();

                return;
            }

            if ($details['state'] == Api::STATUS_FAILED) {
                $request->markFailed();

                return;
            }

            $request->markUnknown();

        } else {
            $tokenDetails = $model->getDetails();
            $payment = $this->paymentRepo->findOneBy(['id' => $tokenDetails->getId()]);
            $request->setModel($payment);
        }
    }

    /**
     * @param mixed $request
     *
     * @return bool
     */
    public function supports($request)
    {
        return $request instanceof GetStatusInterface
            && ($request->getModel() instanceof PaymentInterface
            || $request->getModel() instanceof TokenInterface);
    }
}
