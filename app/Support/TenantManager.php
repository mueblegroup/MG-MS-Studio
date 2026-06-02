<?php

namespace App\Support;

use App\Models\Studio;

class TenantManager
{
    protected ?Studio $studio = null;

    public function set(?Studio $studio): void
    {
        $this->studio = $studio;
    }

    public function current(): ?Studio
    {
        return $this->studio;
    }

    public function id(): ?int
    {
        return $this->studio?->id;
    }

    public function clear(): void
    {
        $this->studio = null;
    }
}
