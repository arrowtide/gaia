<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Http\Livewire;

use Livewire\Component;

class CustomerOrders extends Component
{
    public function placeholder()
    {
        return view('customer/profile/orders/_orders_loading');
    }

    public function render()
    {
        return view('customer/profile/orders/_orders');
    }
}
