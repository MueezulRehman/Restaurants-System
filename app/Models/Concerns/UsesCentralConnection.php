<?php

namespace App\Models\Concerns;

/**
 * Keeps platform/control-plane models on the central database even while a
 * request is temporarily running inside a tenant connection.
 */
trait UsesCentralConnection
{
    public function getConnectionName(): ?string
    {
        return config('tenancy.central_connection', config('database.default'));
    }
}
