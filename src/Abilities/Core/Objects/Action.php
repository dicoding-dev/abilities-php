<?php

namespace Abilities\Core\Objects;

use InvalidArgumentException;

class Action
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(private string $action = '*')
    {
        $this->action = trim($this->action);
        if (empty($this->action)) {
            throw new InvalidArgumentException('Action must not be empty');
        }

        if (!preg_match('/^(([a-zA-Z0-9_\-])+|([*]){1})$/', $this->action)) {
            throw new InvalidArgumentException(
                'Invalid action naming. Please use a combination of lowercase letter, number, dash and underscore only or a single star (*) character'
            );
        }
    }

    public function get(): string
    {
        return $this->action;
    }

    public function __toString(): string
    {
        return $this->get();
    }
}
