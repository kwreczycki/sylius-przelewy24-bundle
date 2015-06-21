<?php

namespace KW\Bundle\SyliusPrzelewy24Bundle\Tests\Payum\Przelewy24\Fixtures;

class ApiFixtures {

    public $sandbox = true;
    public $gatewayId = 1234;
    public $crcKey = "sdsds323x//11";
    public $returnUrlDomain = 'http://localhost:8000';
    public $statusPaymentUrl = 'https://sandbox.przelewy24.pl/transakcja.php';

    public function createModel()
    {
        return [
            'p24_session_id' => '1212121',
            'p24_desc' => 'foo',
            'p24_amount' => 1234,
            'p24_email' => 'foo@bar.com',
            'hash' => '1212xx323sd1212'
        ];
    }

    public function createPostResponse()
    {
        return [
            'p24_session_id' => 'sd1121212',
            'p24_order_id' => 1,
            'p24_id_sprzedawcy' => 2,
            'p24_amount' => 1111,
            'p24_crc' => 'sdsdsdxx23232323'
        ];
    }

    public function getStatusPaymentFormParams()
    {
        return [
            'form_params' => [
                'p24_id_sprzedawcy' => 1234,
                'p24_session_id' => 'sd1121212',
                'p24_order_id' => 1,
                'p24_kwota' => 1111,
                'p24_crc' => '2b9624a30eaae0ea012436b67d89b87c'
            ]
        ];
    }

    public function createSuccessResponseBody()
    {
        return 'RESULT
TRUE';
    }

} 