<?php

namespace Bryceandy\Selcom\Tests\Feature\Checkout;

use Bryceandy\Selcom\{
    Exceptions\InvalidDataException,
    Exceptions\MissingDataException,
    Facades\Selcom,
    Tests\TestCase,
};
use Illuminate\{
    Foundation\Testing\RefreshDatabase,
    Foundation\Testing\WithFaker,
    Http\RedirectResponse,
    Support\Arr,
    Support\Facades\Http};

class CheckoutTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    private array $requiredCheckoutData;

    private array $cardCheckoutData;

    private array $walletPaymentResponseData;

    private array $storedCardsResponseData;

    private array $cardPaymentResponseData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requiredCheckoutData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'amount' => $this->faker->randomNumber(5),
            'transaction_id' => strtoupper($this->faker->bothify('##???#??#???')),
        ];

        $this->cardCheckoutData = array_merge($this->requiredCheckoutData, [
            'address' => $this->faker->address(),
            'postcode' => $this->faker->postcode(),
        ]);

        $createOrderResponse = Http::response(json_decode(
            file_get_contents(__DIR__ . '/../../stubs/create-order-response.json'),
            true
        ));

        $this->walletPaymentResponseData = json_decode(
            file_get_contents(__DIR__ . '/../../stubs/wallet-payment-response.json'),
            true
        );

        $this->storedCardsResponseData = json_decode(
            file_get_contents(__DIR__ . '/../../stubs/stored-cards-response.json'),
            true
        );

        $this->cardPaymentResponseData = json_decode(
            file_get_contents(__DIR__ . '/../../stubs/card-payment-response.json'),
            true
        );

        $urlPrefix = 'selcommobile.com/v1/checkout/';

        Http::fake([
            "${urlPrefix}create-order-minimal" => $createOrderResponse,
            "${urlPrefix}create-order" => $createOrderResponse,
            "${urlPrefix}wallet-payment" => Http::response($this->walletPaymentResponseData),
            "${urlPrefix}stored-cards*" => Http::response($this->storedCardsResponseData),
            "${urlPrefix}card-payment" => Http::response($this->cardPaymentResponseData),
        ]);
    }

    /** @test */
    public function test_sending_incomplete_checkout_data_throws_an_exception()
    {
        $this->expectException(MissingDataException::class);

        Selcom::checkout(Arr::except(
            $this->requiredCheckoutData,
            Arr::random(array_keys($this->requiredCheckoutData))
        ));

        $response = Selcom::checkout($this->requiredCheckoutData);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function test_sending_incomplete_card_checkout_data_throws_an_exception()
    {
        $this->expectException(MissingDataException::class);

        Selcom::cardCheckout(Arr::except(
            $this->cardCheckoutData,
            Arr::random(array_keys($this->cardCheckoutData))
        ));

        $response = Selcom::cardCheckout($this->cardCheckoutData);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function test_sending_incomplete_checkout_name_throws_an_exception()
    {
        $this->expectException(InvalidDataException::class);

        $this->expectExceptionMessage('Name must contain at-least 2 words');

        $data = $this->requiredCheckoutData;

        $data['name'] = 'Bryce';

        Selcom::checkout($data);
    }

    /** @test */
    public function test_sending_incomplete_card_checkout_name_throws_an_exception()
    {
        $this->expectException(InvalidDataException::class);

        $this->expectExceptionMessage('Name must contain at-least 2 words');

        $data = $this->cardCheckoutData;

        $data['name'] = 'Bryce';

        Selcom::cardCheckout($data);
    }

    /** @test */
    public function test_ussd_checkout_sends_back_data_without_redirecting()
    {
        $response = Selcom::checkout(array_merge(
            $this->requiredCheckoutData,
            ['no_redirection' => true]
        ));

        $this->assertEquals($response, $this->walletPaymentResponseData);
    }

    /** @test */
    public function test_automatic_card_checkout_requires_user_data()
    {
        $this->expectException(InvalidDataException::class);

        $this->expectExceptionMessage(
            'You are missing the following: user_id & buyer_uuid. Otherwise, set no_redirection to false'
        );

        $data = $this->cardCheckoutData;

        $data['no_redirection'] = true;

        Selcom::cardCheckout($data);
    }

    /** @test */
    public function test_automatic_card_payment_sends_data_without_redirecting()
    {
        $response = Selcom::cardCheckout(array_merge($this->cardCheckoutData, [
            'no_redirection' => true,
            'user_id' => $this->faker->randomNumber(),
            'buyer_uuid' => $this->faker->uuid(),
        ]));

        $this->assertEquals($response, $this->cardPaymentResponseData);
    }
}
