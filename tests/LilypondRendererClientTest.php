<?php

use Orchestra\Testbench\TestCase;
use ProScholy\LilypondRenderer\Client;

class LilypondRendererClientTest extends TestCase
{
    protected $client;

    protected function getPackageProviders($app)
    {
        return ['ProScholy\LilypondRenderer\LilypondRendererServiceProvider'];
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->client = new Client();
    }

    public function testApiToken()
    {
        $this->assertEquals(config('bohemia_erp.api_token'),
            $this->client->get('api/token-echo'));
    }

    public function testUser()
    {
        $us = $this->client->get('api/check');

        $this->assertEquals($us->id, 1);
    }

    public function testStoreOrder()
    {
        $o = new Order([
            'title' => 'Order 1',
            'invoicing_model' => 1,
            'renewable' => 0,
            'renew_unit' => 'month', 
            'renew_amount' => 1,
            'currency' => 'czk'
        ]);

        $res = $this->client->storeOrder($o, 1, 1);

        $this->assertEquals($res->description, "Order 1");

        return $res->id;
    }

    public function testStoreOrderBadCurrency()
    {
        $o = new Order([
            'title' => 'Order 1',
            'invoicing_model' => 1,
            'renewable' => 0,
            'renew_unit' => 'month', 
            'renew_amount' => 1,
            'currency' => 'abc'
        ]);

        $this->expectException(Exception::class);
        $res = $this->client->storeOrder($o, 1, 1);
    }

    /**
     * @depends testStoreOrder
     */
    public function testConfirmOrder($order_id)
    {
        $confirmed = $this->client->confirmStagingOrder($order_id);
        $this->assertEquals(1, $confirmed->status);

        $this->assertAttributeNotEmpty('invoices', $confirmed);

        return $confirmed;
    }

    public function testStoreOrderNotToBeConfirmed()
    {
        $o = new Order([
            'title' => 'Order unconfirmed',
            'invoicing_model' => 1,
            'renewable' => 0,
            'renew_unit' => 'month', 
            'renew_amount' => 1,
            'currency' => 'czk'
        ]);

        $res = $this->client->storeOrder($o, 1, 1);

        $this->assertEquals($res->description, "Order unconfirmed");

        return $res->id;
    }

    public function testStoreOrderNonExistingCompany()
    {
        $o = new Order([
            'title' => 'Order 1',
            'invoicing_model' => 1,
            'renewable' => 0,
            'renew_unit' => 'month', 
            'renew_amount' => 1,
            'currency' => 'czk'
        ]);

        $this->expectException(ERPApiException::class);
        $res = $this->client->storeOrder($o, 1234, 1);

        return $res->id;
    }

    /**
     * @depends testStoreOrder
     */
    public function testGetOrder($order_id)
    {
        $this->assertEquals($order_id, $this->client->getOrder($order_id)->id);
    }

    public function testCreateRecipient()
    {
        $r = new Recipient([
            'name' => 'Miroslav',
            'surname' => 'Sery',
            'type' => 0
        ]);

        $recipient = $this->client->createRecipient($r, 1);
        $this->assertEquals(1, $recipient->contractor_id);

        return $recipient;
    }

    public function testCreateRecipientUnsuccessful()
    {
        $r = new Recipient([
            'name' => 'Miroslav',
            'surname' => 'Sery',
            'type' => 0
        ]);

        $this->expectException(Exception::class);
        $recipient = $this->client->createRecipient($r, 2); // 2 has only read permissions
    }
    
    /**
     * @depends testCreateRecipient
     */
    public function testUpdateRecipient($recipient)
    {
        $r = new Recipient([
            'type' => 1
        ]);

        $updated = $this->client->updateRecipient($r, $recipient->id, 1);

        $this->assertEquals(1, $updated->type);
    }

    /**
     * @depends testStoreOrder
     */
    public function testStoreOrderItem($order_id)
    {
        $oitem = new OrderItem([
            'code' => 'abcdefgh',
            'description' => 'order item 1',
            'price' => 101,
            'vat_rate' => 21,
            'is_price_with_vat' => true
        ]);

        $order_item = $this->client->storeOrderItem($oitem, $order_id);
        $this->assertEquals($order_id, $order_item->order_id);

        return $order_id;
    }

    /**
     * @depends testStoreOrderNotToBeConfirmed
     */
    public function testStoreOrderItem2($order_id)
    {
        $oitem = new OrderItem([
            'code' => 'abcdefgh',
            'description' => 'order item 1',
            'price' => 100,
            'vat_rate' => 21
        ]);

        $order_item = $this->client->storeOrderItem($oitem, $order_id);
        $this->assertEquals($order_id, $order_item->order_id);

        return $order_id;
    }

    /** 
     * @depends testStoreOrderItem
     */
    public function testGetOrderItems($order_id)
    {
        $order_items = $this->client->getOrderItems($order_id);

        $this->assertTrue($order_items[0]->order_id == $order_id);
    }

    /**
     * @depends testConfirmOrder
     */
    public function testStoreTransaction($confirmed)
    {
        $amount = 101;

        $resp = $this->client->storeTransaction(Transaction::TYPE_MANUAL_BANK_TRANSFER, $amount, $confirmed->id, 1);

        $this->assertEquals(101, $resp->amount);

        return $confirmed;
    }

    /**
     * @depends testStoreTransaction
     */
    public function testGetInvoiceFile($confirmed)
    {
        $file = $this->client->getInvoiceFile($confirmed->invoices[0]->id);

        $this->assertTrue($file instanceof \GuzzleHttp\Psr7\Stream);
    }   

}