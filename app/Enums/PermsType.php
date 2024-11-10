<?php

namespace App\Enums;

enum PermsType: int
{
    case CAN_DELETE_SERVER = 1 << 1;
    case CAN_EDIT_SERVER = 1 << 2;
    case CAN_CREATE_CHANNEL = 1 << 3;
    case CAN_DELETE_CHANNEL = 1 << 4;
    case CAN_EDIT_CHANNEL = 1 << 5;
    case CAN_CREATE_MESSAGE = 1 << 6;
    case CAM_CREATE_ATTACHMENTS = 1 << 7;
    case CAN_DELETE_MESSAGE = 1 << 8;
    case CAN_EDIT_MESSAGE = 1 << 9;
    case CAN_CREATE_ROLE = 1 << 10;
    case CAN_DELETE_ROLE = 1 << 11;
    case CAN_EDIT_ROLE = 1 << 12;
    case CAN_MANAGE_MESSAGE = 1 << 13;
    case CAN_MANAGE_ROLE = 1 << 14;
    case CAN_MANAGE_SERVER = 1 << 15;
    case CAN_SEE_CHANNEL = 1 << 16;
    case CAN_INVITE = 1 << 17;
    case CAN_KICK = 1 << 18;

    public function has(PermsType|int $perm): bool
    {
        return $perm == PermsType::CAN_CREATE_CHANNEL;
    }
}
