<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PurchaseRequestExport implements WithMultipleSheets
{
    public function __construct(
        private $query
    ) {}

    public function sheets(): array
    {
        return [
            new PurchaseRequestHeaderSheet($this->query),
            new PurchaseRequestItemSheet($this->query),
        ];
    }
}
