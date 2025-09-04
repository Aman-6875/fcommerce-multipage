<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\FacebookPage;
use Illuminate\Auth\Access\HandlesAuthorization;

class FacebookPagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the client can view any Facebook pages.
     */
    public function viewAny(Client $client): bool
    {
        return true;
    }

    /**
     * Determine whether the client can view the Facebook page.
     */
    public function view(Client $client, FacebookPage $facebookPage): bool
    {
        return $client->id === $facebookPage->client_id;
    }

    /**
     * Determine whether the client can create Facebook pages.
     */
    public function create(Client $client): bool
    {
        return $client->canAddNewPage();
    }

    /**
     * Determine whether the client can update the Facebook page.
     */
    public function update(Client $client, FacebookPage $facebookPage): bool
    {
        return $client->id === $facebookPage->client_id;
    }

    /**
     * Determine whether the client can delete the Facebook page.
     */
    public function delete(Client $client, FacebookPage $facebookPage): bool
    {
        return $client->id === $facebookPage->client_id;
    }

    /**
     * Determine whether the client can manage the Facebook page.
     */
    public function manage(Client $client, FacebookPage $facebookPage): bool
    {
        return $client->id === $facebookPage->client_id;
    }

    /**
     * Determine whether the client can test the connection.
     */
    public function testConnection(Client $client, FacebookPage $facebookPage): bool
    {
        return $client->id === $facebookPage->client_id;
    }

    /**
     * Determine whether the client can disconnect the page.
     */
    public function disconnect(Client $client, FacebookPage $facebookPage): bool
    {
        return $client->id === $facebookPage->client_id;
    }
}