<?php

namespace App\Services\Base;

use App\Models\Base\BaseBank;
use App\Services\MasterService;

class BaseBankService extends MasterService
{
    public function getAllBaseBank()
    {
        return BaseBank::active()->get();
    }
}
