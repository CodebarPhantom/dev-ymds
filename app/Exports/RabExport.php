<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RabExport implements WithMultipleSheets
{
    public function __construct(
        private array $filters,
        private User $user
    ) {}

    public function sheets(): array
    {
        return [
            new RabHeaderSheet($this->filters, $this->user),
            new RabItemSheet($this->filters, $this->user),
        ];
    }
}
