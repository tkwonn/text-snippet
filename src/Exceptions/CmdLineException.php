<?php

// Similar to UsageException but is intended for diagnosing errors in the command-line arguments specified to a program

namespace Exceptions;

class CmdLineException extends \Exception
{
    public static function aliasNotFound(string $alias): self
    {
        return new self(sprintf("Could not find command alias '%s'", $alias));
    }

    public static function missingRequiredValue(string $alias): self
    {
        return new self(sprintf("Command '%s' requires a value", $alias));
    }

    public static function invalidOptionFormat(): self
    {
        return new self('Invalid option format. Options must start with - or --');
    }

    public static function missingRequiredArgument(string $argument): self
    {
        return new self(sprintf("Required argument '--%s' not found", $argument));
    }
}
