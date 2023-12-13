<?php

namespace Abilities;

class Test {
    public static function a(): bool {
        return true;
    }

    public static function formatString(string $someString): string
    {
        return "format:$someString";
    }

}