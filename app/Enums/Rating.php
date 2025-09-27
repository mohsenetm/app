<?php

namespace App\Enums;

enum Rating: string
{
    case AGAIN = 'again';
    case HARD = 'hard';
    case GOOD = 'good';
    case EASY = 'easy';
}
