<?php

namespace App\Services\Base;

use App\Services\MasterService;
use App\Models\Base\BasePtkp;

class BasePtkpService extends MasterService
{
    public function getAllPtkp()
    {
        return BasePtkp::get();
    }
}
