<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case NEW = 'new';
    case LEARNING = 'learning';
    case REVIEW = 'review';
    case RELEARNING = 'relearning';
}
