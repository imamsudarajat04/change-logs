<?php

namespace Imamsudarajat04\ChangeLogs\Enums;

enum RecordAction: string
{
    case CREATE = 'CREATE';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
    case RESTORE = 'RESTORE';
}
