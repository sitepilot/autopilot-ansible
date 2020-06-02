<?php

namespace App\Contracts;

use App\Server;

interface ProvisionableResource
{
    /**
     * Get the tasks for the resource.
     * 
     * @return HasMany
     */
    public function tasks();
}
