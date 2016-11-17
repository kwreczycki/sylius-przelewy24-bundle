<?php

namespace KW\Bundle\SyliusPrzelewy24Bundle\Payum\Przelewy24\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Request;

class CaptureAction extends PaymentAwareAction
{
    /** @var Request */
    private $httpRequest;

    public function setRequest(Request $request = null)
    {
        $this->httpRequest = $request;
    }

    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $payment PaymentInterface */
        $payment = $request->getModel();

        $this->composeDetails($payment, $request->getToken());

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        try {
            $request->setModel($details);
            $this->payment->execute($request);

            $payment->setDetails($details);
        } catch (\Exception $e) {
            $payment->setDetails($details);

            throw $e;
        }
    }

    private function composeDetails(PaymentInterface $payment, TokenInterface $token)
    {
        $order = $payment->getOrder();

        $details = [];
        $details['hash'] = $token->getHash();
        $details['p24_amount'] = $order->getTotal();
        $details['p24_email'] = $order->getCustomer()->getEmail();
        $details['p24_payment_id'] = $payment->getId();
        $details['p24_session_id'] = $payment->getId() . time();
        $details['p24_desc'] = sprintf("Zamówienie zawierające %d produktów na całkowitą kwotę %01.2f",
            $order->getItems()->count(), $order->getTotal() / 100);

        $payment->setDetails($details);
    }

    /**
     * @param mixed $request
     *
     * @return bool
     */
    public function supports($request)
    {
        return $request instanceof Capture &&
            $request->getModel() instanceof PaymentInterface;
    }
}
