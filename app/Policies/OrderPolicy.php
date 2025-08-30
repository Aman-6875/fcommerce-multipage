<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Client $client): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Client $client, Order $order): bool
    {
        return $client->id === $order->client_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Client $client): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Client $client, Order $order): bool
    {
        return $client->id === $order->client_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Client $client, Order $order): bool
    {
        return $client->id === $order->client_id && 
               in_array($order->status, ['pending', 'cancelled']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Client $client, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Client $client, Order $order): bool
    {
        return false;
    }
}
