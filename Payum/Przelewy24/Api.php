<?php

namespace KW\Bundle\SyliusPrzelewy24Bundle\Payum\Przelewy24;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Payum\Core\Bridge\Spl\ArrayObject;

class Api
{
    const STATUS_SUCCESS = 'TRUE';
    const STATUS_FAILED = 'err00';
    const CURRENCY = 'PLN';

    /** @var string */
    private $gatewayId;
    /** @var string */
    private $crcKey;
    /** @var ClientInterface */
    private $httpClient;
    /** @var string */
    private $sandbox;
    /** @var string*/
    private $returnUrlDomain;

    public function __construct($sandbox = true, $gatewayId, $crcKey, $returnUrlDomain)
    {
        $this->sandbox = $sandbox;
        $this->gatewayId = $gatewayId;
        $this->crcKey = $crcKey;
        $this->returnUrlDomain = $returnUrlDomain;
    }

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function buildFormParamsForPostRequest(array $details)
    {
        $params = [
            'p24_session_id' => $details['p24_session_id'],
            'p24_opis' => $details['p24_desc'],
            'p24_id_sprzedawcy' => $this->gatewayId,
            'p24_kwota' => $details['p24_amount'],
            'p24_email' => $details['p24_email'],
            'p24_return_url_ok' => sprintf('%s/payment/capture/%s', $this->returnUrlDomain, $details['hash']),
            'p24_return_url_error' => sprintf('%s/payment/capture/%s', $this->returnUrlDomain, $details['hash']),
            'p24_sign' => $this->createHashForNewPayment($details)
        ];

        return $params;
    }

    public function getPaymentStatus(ArrayObject $notificationResponse)
    {
        if (!isset($notificationResponse['p24_session_id']) || !isset($notificationResponse['p24_order_id']) ||
            !isset($notificationResponse['p24_amount'])) {
            throw new \InvalidArgumentException("Missing one of parameter.");
        }

        try {
            $response = $this->httpClient->post(
                $this->getStatusPaymentUrl(), [
                    'form_params' => [
                        'p24_id_sprzedawcy' => $this->gatewayId,
                        'p24_session_id' => $notificationResponse['p24_session_id'],
                        'p24_order_id' => $notificationResponse['p24_order_id'],
                        'p24_kwota' => $notificationResponse['p24_amount'],
                        'p24_sign' => $this->createHashForPaymentStatus(
                            $notificationResponse->toUnsafeArray()
                        )
                    ]
                ]
            );

            return $this->parseResponse($response->getBody());

        } catch (RequestException $requestException) {
            throw new \RuntimeException($requestException->getMessage());
        }
    }

    public function getStatusPaymentUrl()
    {
        return $this->sandbox ?
            "https://sandbox.przelewy24.pl/transakcja.php" :
            "https://przelewy24.pl/transakcja.php";
    }

    public function getNewPaymentUrl()
    {
        return $this->sandbox ?
            "https://sandbox.przelewy24.pl/index.php" :
            "https://secure.przelewy24.pl/index.php";
    }

    private function parseResponse($response)
    {
        $responseArray = explode("\n", $response);

        if (count($responseArray) > 2) {
            $code = $responseArray[2];
        } else {
            $code = $responseArray[1];
        }
        return $code;
    }

    private function createHashForNewPayment(array $details)
    {
        return $this->createHash(
            $details,
            $this->gatewayId
        );
    }

    private function createHashForPaymentStatus(array $details)
    {
        return $this->createHash(
            $details,
            $details['p24_order_id']
        );
    }

    private function createHash(array $details, $gatewayIdOrOrderId)
    {
        return md5(
            $details['p24_session_id'] . '|' .
            $gatewayIdOrOrderId . '|' .
            $details['p24_amount'] . '|' .
            self::CURRENCY . '|' .
            $this->crcKey
        );
    }
}
