<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in progress';
    case Completed = 'completed';
}
