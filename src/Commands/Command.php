<?php
// An interface defining the methods that all commands have.

namespace Commands;

interface Command
{
    public static function getAlias(): string;

    /** @return Argument[]  */
    public static function getArguments(): array;

    public static function getHelp(): string;

    public static function isCommandValueRequired(): bool;

    /** @return bool | string
     * If a value exists, it returns the string value or true if a parameter exists.
     * If no argument is set, it returns false.
     */
    public function getArgumentValue(string $arg): bool | string;

    public function execute(): int;
}
