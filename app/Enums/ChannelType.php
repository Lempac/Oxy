<?php

namespace App\Enums;

enum ChannelType: string
{
    case Text = 'text';
    case Voice = 'voice';
}
