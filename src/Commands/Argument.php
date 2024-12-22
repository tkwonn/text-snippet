<?php
/*
 * Builder class that defines the arguments available for a command.
 * Commands need to create all optional arguments as needed.
 * Using the builder allows further customization of the arguments,
 * such as whether a value is required or if a short version of the argument is allowed.
*/

namespace Commands;

class Argument
{
    private string $argument;
    private string $description = '';
    private bool $required = true;
    private bool $allowAsShort = false;

    public function __construct(string $argument)
    {
        $this->argument = $argument;
    }

    public function description(string $description): Argument
    {
        $this->description = $description;

        return $this;
    }

    public function required(bool $required): Argument
    {
        $this->required = $required;

        return $this;
    }

    public function allowAsShort(bool $allowAsShort): Argument
    {
        $this->allowAsShort = $allowAsShort;

        return $this;
    }

    public function getArgument(): string
    {
        return $this->argument;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isShortAllowed(): bool
    {
        return $this->allowAsShort;
    }
}
