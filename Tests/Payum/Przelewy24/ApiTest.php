<?php

namespace KW\Bundle\SyliusPrzelewy24Bundle\Tests\Payum\Przelewy24;

use KW\Bundle\SyliusPrzelewy24Bundle\Payum\Przelewy24\Api;
use KW\Bundle\SyliusPrzelewy24Bundle\Tests\Payum\Przelewy24\Fixtures\ApiFixtures;
use KW\Bundle\SyliusPrzelewy24Bundle\Tests\TestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Payum\Core\Bridge\Spl\ArrayObject;

class ApiTest extends TestCase
{
    /** @test */
    public function it_should_build_form_parameters_for_http_post_request()
    {
        $model = $this->apiFixtures->createModel();
        $formFields = $this->api->buildFormParamsForPostRequest($model);
        $this->assertArrayHasKeys([
                'p24_session_id',
                'p24_opis',
                'p24_id_sprzedawcy',
                'p24_kwota',
                'p24_email',
                'p24_return_url_ok',
                'p24_return_url_error',
                'p24_sign'
            ],
            $formFields
        );
    }

    /** @test */
    public function it_should_throw_exception_if_one_of_parameters_from_return_response_is_missing()
    {
        $this->setExpectedException("\\InvalidArgumentException", "Missing one of parameter.");

        $postResponse = $this->apiFixtures->createPostResponse();
        unset($postResponse['p24_session_id']);

        $model = ArrayObject::ensureArrayObject($postResponse);
        $this->api->getPaymentStatus($model);
    }

    /** @test */
    public function it_should_send_post_request_and_return_success_response_for_payment()
    {
        $postResponse = $this->apiFixtures->createPostResponse();
        $model = ArrayObject::ensureArrayObject($postResponse);

        $streamedResponse = \Mockery::mock(Response::class);
        $streamedResponse->shouldReceive('getBody')->andReturn($this->apiFixtures->createSuccessResponseBody());

        $this->httpClient->shouldReceive('post')->withArgs([
                $this->apiFixtures->statusPaymentUrl,
                $this->apiFixtures->getStatusPaymentFormParams()
            ])->once()->andReturn($streamedResponse);

        $response = $this->api->getPaymentStatus($model);
        $this->assertEquals('TRUE', $response);
    }

    public function setUp()
    {
        $this->apiFixtures = new ApiFixtures();

        $this->api = new Api(
            $this->apiFixtures->sandbox,
            $this->apiFixtures->gatewayId,
            $this->apiFixtures->crcKey,
            $this->apiFixtures->returnUrlDomain
        );

        $this->httpClient = \Mockery::mock(ClientInterface::class);
        $this->api->setHttpClient($this->httpClient);
    }

    /** @var \Mockery\Mock */
    private $httpClient;

    /** @var Api */
    private $api;

    /** @var ApiFixtures */
    private $apiFixtures;
}
