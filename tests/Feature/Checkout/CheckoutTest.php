<?php

namespace Bryceandy\Selcom\Tests\Feature\Checkout;

use Bryceandy\Selcom\{Events\CheckoutWebhookReceived,
    Exceptions\InvalidDataException,
    Exceptions\MissingDataException,
    Facades\Selcom,
    Tests\TestCase};
use Illuminate\{Foundation\Testing\RefreshDatabase,
    Foundation\Testing\WithFaker,
    Http\RedirectResponse,
    Support\Arr,
    Support\Facades\DB,
    Support\Facades\Event,
    Support\Facades\Http};
use Mockery;

class CheckoutTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    private array $requiredCheckoutData;

    private array $cardCheckoutData;

    private array $walletPaymentResponseData;

    private array $storedCardsResponseData;

    private array $cardPaymentResponseData;

    private array $okResponseData;

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

        $this->okResponseData = json_decode(
            file_get_contents(__DIR__ . '/../../stubs/ok-response-data.json'),
            true
        );

        $urlPrefix = 'selcommobile.com/v1/checkout/';

        Http::fake([
            "${urlPrefix}create-order-minimal" => $createOrderResponse,
            "${urlPrefix}create-order" => $createOrderResponse,
            "${urlPrefix}wallet-payment" => Http::response($this->walletPaymentResponseData),
            "${urlPrefix}card-payment" => Http::response($this->cardPaymentResponseData),
            "${urlPrefix}delete-card" => Http::response($this->okResponseData),
            "${urlPrefix}order-status*" => Http::response($this->okResponseData),
            "${urlPrefix}list-orders*" => Http::response($this->okResponseData),
            "${urlPrefix}cancel-order" => Http::response($this->okResponseData),
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
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
    public function test_checkout_redirects_to_the_gateway_page()
    {
        $response = Selcom::checkout($this->requiredCheckoutData);

        $this->assertTrue($response instanceof RedirectResponse);
    }

    /** @test */
    public function test_automatic_card_checkout_requires_user_data()
    {
        $this->expectException(InvalidDataException::class);

        $this->expectExceptionMessage(
            'You are missing the following: user_id. Otherwise, set no_redirection to false'
        );

        $data = $this->cardCheckoutData;

        $data['no_redirection'] = true;

        Selcom::cardCheckout($data);
    }

    /** @test */
    public function test_automatic_card_payment_sends_data_without_redirecting()
    {
        Http::fake([
            "selcommobile.com/v1/checkout/stored-cards*" => Http::response($this->storedCardsResponseData),
        ]);

        $response = Selcom::cardCheckout(array_merge(
            $this->cardCheckoutData,
            [
                'no_redirection' => true,
                'user_id' => $this->faker->randomNumber(),
            ],
            // Randomly include uuid
            (Arr::random([0, 1]) ? ['buyer_uuid' => $this->faker->uuid()] : [])
        ));

        $this->assertEquals($response, $this->cardPaymentResponseData);
    }

    /** @test */
    public function test_automatic_card_payment_without_created_cards_throws_an_exception()
    {
        Http::fake([
            "selcommobile.com/v1/checkout/stored-cards*" => Http::response(['data' => []]),
        ]);

        $this->expectException(InvalidDataException::class);

        $this->expectExceptionMessage("User doesn't have stored cards!");

        Selcom::cardCheckout(array_merge($this->cardCheckoutData, [
            'no_redirection' => true,
            'user_id' => $this->faker->randomNumber(),
            'buyer_uuid' => $this->faker->uuid(),
        ]));
    }

    /** @test */
    public function test_order_details_are_saved_before_card_payments()
    {
        Http::fake([
            "selcommobile.com/v1/checkout/stored-cards*" => Http::response($this->storedCardsResponseData),
        ]);

        $data = array_merge($this->cardCheckoutData, [
            'no_redirection' => true,
            'user_id' => $this->faker->randomNumber(),
            'buyer_uuid' => $this->faker->uuid(),
        ]);

        $response = Selcom::cardCheckout($data);

        $this->assertDatabaseHas('selcom_payments', [
            'user_id' => $data['user_id'],
            'gateway_buyer_uuid' => $data['buyer_uuid'],
            'transid' => $data['transaction_id'],
            'amount' => $data['amount'],
        ]);

        $this->assertEquals($response, $this->cardPaymentResponseData);
    }

    /** @test */
    public function test_order_details_are_saved_before_checkout_payments()
    {
        $data = array_merge($this->requiredCheckoutData, ['no_redirection' => true]);

        $response = Selcom::checkout($data);

        $this->assertDatabaseHas('selcom_payments', [
            'transid' => $data['transaction_id'],
            'amount' => $data['amount'],
        ]);

        $this->assertEquals($response, $this->walletPaymentResponseData);
    }

    /** @test */
    public function test_stored_cards_can_be_fetched()
    {
        Http::fake([
            "selcommobile.com/v1/checkout/stored-cards*" => Http::response($this->storedCardsResponseData),
        ]);

        $response= Selcom::fetchCards($this->faker->randomNumber(), $this->faker->uuid());

        $this->assertEquals($response, $this->storedCardsResponseData);
    }

    /** @test */
    public function test_cards_can_be_deleted()
    {
        $response = Selcom::deleteCard($this->faker->randomNumber(), $this->faker->uuid());

        $this->assertEquals($response, $this->okResponseData);
    }

    /** @test */
    public function test_webhook_updates_payment_records()
    {
        $data = $this->requiredCheckoutData;

        Selcom::checkout($data);

        $this->assertDatabaseHas('selcom_payments', [
            'transid' => $data['transaction_id'],
            'payment_status' => null,
        ]);

        $orderId = DB::table('selcom_payments')
            ->where('transid', $data['transaction_id'])
            ->value('order_id');

        $this->post(route('selcom.checkout-callback'), [
            'transid' => $data['transaction_id'],
            'order_id' => $orderId,
            'reference' => '289124234',
            'result' => 'SUCCESS',
            'resultcode' => '000',
            'payment_status' => 'COMPLETED',
        ])
            ->assertOk();

        $this->assertDatabaseHas('selcom_payments', [
            'transid' => $data['transaction_id'],
            'payment_status' => 'COMPLETED',
            'order_id' => $orderId,
            'reference' => '289124234',
        ]);
    }

    /** @test */
    public function test_webhook_dispatches_an_event()
    {
        $data = $this->requiredCheckoutData;

        Selcom::checkout($data);

        $orderId = DB::table('selcom_payments')
            ->where('transid', $data['transaction_id'])
            ->value('order_id');

        Event::fake();

        $this->post(route('selcom.checkout-callback'), [
            'transid' => $data['transaction_id'],
            'order_id' => $orderId,
            'reference' => '289124234',
            'result' => 'SUCCESS',
            'resultcode' => '000',
            'payment_status' => 'COMPLETED',
        ])
            ->assertOk();

        Event::assertDispatched(
            fn (CheckoutWebhookReceived $event) => $event->orderId === $orderId
        );
    }

    /** @test */
    public function test_order_statuses_can_be_queried()
    {
        $response = Selcom::orderStatus($this->faker->uuid());

        $this->assertEquals($response, $this->okResponseData);
    }

    /** @test */
    public function test_orders_can_be_listed()
    {
        $response = Selcom::listOrders($this->faker->date(), $this->faker->date());

        $this->assertEquals($response, $this->okResponseData);
    }

    /** @test */
    public function test_orders_can_be_cancelled()
    {
        $response = Selcom::cancelOrder($this->faker->uuid());

        $this->assertEquals($response, $this->okResponseData);
    }
}
