<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Listeners;

use Arrowtide\Gaia\Interfaces\CartRepositoryInterface;
use Illuminate\Auth\Events\Logout;

class HandleUserLogout
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository
    ) {}

    public function handle(Logout $event): void
    {
        // TODO :: Handle the cart when a user logs out, so another user cannot see the previous cart.
    }
}
