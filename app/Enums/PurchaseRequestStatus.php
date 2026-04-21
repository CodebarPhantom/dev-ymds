<?php

namespace App\Enums;

enum PurchaseRequestStatus: string
{
    case DRAFT                = 'DRAFT';
    case PENDING_BENDAHARA    = 'PENDING_BENDAHARA';
    case REJECTED_BENDAHARA   = 'REJECTED_BENDAHARA';
    case PENDING_KETUA        = 'PENDING_KETUA';
    case REJECTED_KETUA       = 'REJECTED_KETUA';
    case APPROVED             = 'APPROVED';
    case CANCELLED            = 'CANCELLED';
    case PURCHASING           = 'PURCHASING';
    case PARTIALLY_PURCHASED  = 'PARTIALLY_PURCHASED';
    case COMPLETED            = 'COMPLETED';
}
