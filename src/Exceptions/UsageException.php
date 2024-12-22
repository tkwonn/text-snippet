<?php

// Used to diagnose errors in command-line argument usage

namespace Exceptions;

class UsageException extends \Exception
{
    private static function getAvailableCommands(): string
    {
        return "Available commands:\n" .
            "  code-gen   Generate boilerplate code\n" .
            "  migrate    Run database migrations\n" .
            "  seed       Seed the database with test data\n\n" .
            "For more information about a command, run:\n" .
            'php console {command} --help';
    }

    public static function noCommandSpecified(): self
    {
        return new self(
            "Command not specified\n\n" .
            "Usage:\n" .
            "  php console {command} {command_value} {--option1} {option1_value} ... {--optionN} {optionN_value}\n\n" .
            static::getAvailableCommands()
        );
    }

    public static function commandNotFound(string $command): self
    {
        return new self(
            "Command '{$command}' not found\n\n" .
            static::getAvailableCommands()
        );
    }
}
