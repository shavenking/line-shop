<?php

namespace Tests\Feature;

use App\Events\GoogleFormSubmitted;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GoogleFormWebhookTest extends TestCase
{
    public function test_main_island_order_is_priced_and_broadcast_immediately()
    {
        Event::fake([GoogleFormSubmitted::class]);

        $response = $this->postJson('/api/google-form-webhook', $this->payload('新竹市東區食品路1號'));

        $response->assertOk();
        $this->assertSame('ok', $response->getContent());

        Event::assertDispatched(GoogleFormSubmitted::class, function (GoogleFormSubmitted $event) {
            return $event instanceof ShouldBroadcastNow
                && $event->data['order_item_total'] === 120
                && $event->data['shipping_fee'] === 160
                && $event->data['total_amount'] === 280
                && $event->data['is_offshore_islands'] === false;
        });
    }

    public function test_penghu_offshore_order_is_priced_and_broadcast_immediately()
    {
        Event::fake([GoogleFormSubmitted::class]);

        $response = $this->postJson('/api/google-form-webhook', $this->payload('澎湖縣馬公市中正路1號'));

        $response->assertOk();
        $this->assertSame('ok', $response->getContent());

        Event::assertDispatched(GoogleFormSubmitted::class, function (GoogleFormSubmitted $event) {
            return $event instanceof ShouldBroadcastNow
                && $event->data['order_item_total'] === 120
                && $event->data['shipping_fee'] === 250
                && $event->data['total_amount'] === 370
                && $event->data['is_offshore_islands'] === true;
        });
    }

    private function payload(string $address): array
    {
        return [
            3 => '王小明',
            4 => '0912000000',
            5 => '300',
            6 => $address,
            7 => '10/26',
            8 => '下午',
            9 => 1,
            16 => 'line-user',
        ];
    }
}
