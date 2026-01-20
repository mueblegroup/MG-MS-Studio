<?php

namespace App\Jobs;

use App\Services\OrderFulfillmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FulfillOrderJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public int $orderId) {}

    public function handle(OrderFulfillmentService $svc): void
    {
        $svc->fulfillPaidOrder($this->orderId);
    }
}
