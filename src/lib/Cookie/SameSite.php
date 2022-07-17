<?php

namespace Sicet7\Cookie;

enum SameSite: string
{
    case EMPTY = '';
    case LAX = 'lax';
    case STRICT = 'strict';
    case NONE = 'none';
}