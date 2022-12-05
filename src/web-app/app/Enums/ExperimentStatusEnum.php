<?php

namespace App\Enums;

enum ExperimentStatusEnum: int
{
    case Idle = 0;
    case Training = 1;
    case Finished = 2;
    case Stopped = 3;
    case Error = 4;
}
