<?php

namespace Abilities\Core;

use Abilities\Core\Exceptions\CompilerException;
use Abilities\Core\Objects\Action;
use Abilities\Core\Objects\Resource;
use Abilities\Core\Objects\Rule;
use Abilities\Core\Objects\Scope;
use JsonException;

class RuleCompiler
{
    /**
     * @throws CompilerException|JsonException
     */
    public static function compile(string $syntax): Rule
    {
        if (empty($syntax)) {
            throw new CompilerException('Syntax must not be empty');
        }

        $leftCursor  = 0;
        $rightCursor = strlen($syntax) - 1;

        $inverted = false;
        if ($syntax[$leftCursor] === '!') {
            $inverted = true;
        }

        $scope  = '';
        $action = '';
        $resource = '';

        $reachedEndScope    = false;
        $reachedEndAction   = false;

        while ($leftCursor <= $rightCursor)
        {
            if (!$reachedEndScope) {
                $left = $syntax[$leftCursor++];

                if ($left !== ':') {
                    $scope .= $left;
                } else {
                    $reachedEndScope = true;
                }
            }

            if (!$reachedEndAction) {
                $right = $syntax[$rightCursor--];

                if ($right !== ':') {
                    $action .= $right;
                } else {
                    $reachedEndAction = true;
                }
            }

            if ($reachedEndScope) {
                $left = $syntax[$leftCursor++];

                if ($left !== '/' && $left !== ':') {
                    $resource .= $left;
                } else {
                    break;
                }
            }
        }

        if (empty($scope)) {
            throw new CompilerException('Scope must not empty');
        }

        if (empty($resource)) {
            throw new CompilerException('Resource must not empty');
        }

        if (empty($action)) {
            throw new CompilerException('Action must not empty');
        }

        $fieldLength = $rightCursor - $leftCursor + 1;
        $field = null;
        if ($fieldLength > 0) {
            $fieldStr = substr($syntax, $leftCursor, $fieldLength);
            if ($fieldStr[0] !== '[' && $fieldStr[0] !== '{') {
                $field = $fieldStr;
            } else {
                $field = json_decode($fieldStr, flags: JSON_THROW_ON_ERROR);
            }
        }

        return new Rule(
            new Scope($scope),
            new Resource(
                $resource,
                $field
            ),
            new Action(strrev($action)),
            $inverted
        );
    }
}
