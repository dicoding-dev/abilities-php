<?php

namespace Abilities\Core\Objects\Enums;

enum FieldType
{
    case ALL;
    case STRING_OR_INT;
    case OBJECT;
    case ARRAY;
}