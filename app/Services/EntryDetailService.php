<?php

namespace App\Services;

use App\Models\EntryDetail;

class EntryDetailService
{
    public function createEntryDetail(array $data)
    {
        return EntryDetail::create($data);
    }
}
