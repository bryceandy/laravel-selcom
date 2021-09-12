<?php

namespace Bryceandy\Selcom\Tests\Feature\Checkout;

use Bryceandy\Selcom\{
    Exceptions\InvalidDataException,
    Exceptions\MissingDataException,
    Facades\Selcom,
    Tests\TestCase,
};
use Illuminate\{
    Foundation\Testing\WithFaker,
    Http\RedirectResponse,
    Support\Arr,
    Support\Facades\Http,
};

class CheckoutTest extends TestCase
{
    use WithFaker;

    private array $requiredData;

    private array $walletPaymentResponseData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requiredData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'amount' => $this->faker->randomNumber(5),
            'transaction_id' => strtoupper($this->faker->bothify('##???#??#???')),
        ];

        $createOrderResponse = Http::response(json_decode(
            file_get_contents(__DIR__ . '/../../stubs/create-order-response.json'),
            true
        ));

        $this->walletPaymentResponseData = json_decode(
            file_get_contents(__DIR__ . '/../../stubs/wallet-payment-response.json'),
            true
        );

        $urlPrefix = 'https://apigwtest.selcommobile.com/v1/';

        Http::fake([
            "${urlPrefix}checkout/create-order-minimal" => $createOrderResponse,
            "${urlPrefix}checkout/create-order" => $createOrderResponse,
            "${urlPrefix}checkout/wallet-payment" => Http::response($this->walletPaymentResponseData),
        ]);
    }

    /** @test */
    public function test_sending_incomplete_checkout_data_throws_an_exception()
    {
        $this->expectException(MissingDataException::class);

        Selcom::checkout(Arr::except(
            $this->requiredData,
            Arr::random(array_keys($this->requiredData))
        ));

        $response = Selcom::checkout($this->requiredData);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function test_sending_incomplete_names_throws_an_exception()
    {
        $this->expectException(InvalidDataException::class);

        $this->expectExceptionMessage('Name must contain at-least 2 words');

        $data = $this->requiredData;

        $data['name'] = 'Bryce';

        Selcom::checkout($data);
    }

    public function test_ussd_checkout_sends_back_data_without_redirecting()
    {
        $response = Selcom::checkout(array_merge($this->requiredData, ['is_ussd' => true]));

        $this->assertEquals($response, $this->walletPaymentResponseData);
    }
}
