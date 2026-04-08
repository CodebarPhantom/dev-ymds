<?php

namespace App\Enums;

enum RabAksiLog: string
{
    case SUBMITTED           = 'SUBMITTED';
    case APPROVED_BENDAHARA  = 'APPROVED_BENDAHARA';
    case REJECTED_BENDAHARA  = 'REJECTED_BENDAHARA';
    case APPROVED_KETUA      = 'APPROVED_KETUA';
    case REJECTED_KETUA      = 'REJECTED_KETUA';
    case CANCELLED           = 'CANCELLED';
}
