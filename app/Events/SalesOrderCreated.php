<?php

namespace App\Events;

use App\Models\SalesOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesOrderCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SalesOrder $salesOrder
    ) {
    }
}

