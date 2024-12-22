<?php
/*
 * Abstract command class that serves as the base for all commands
 * The main job of this base command class is to:
 * - parse all arguments passed through the shell
 * - set up a hash map of all argument-value pairs defined by the command
 * - provides convenient helper methods to child classes (e.g., log method for outputting to stdout)
 */

namespace Commands;

use Exceptions\CmdLineException;

abstract class AbstractCommand implements Command
{
    protected ?string $value;
    protected array $argsMap = [];
    protected static ?string $alias = null;
    protected static bool $requiredCommandValue = false;

    /**
     * @throws CmdLineException When command initialization fails
     */
    public function __construct()
    {
        $this->setUpArgsMap();
    }

    /**
     * @throws CmdLineException When alias is not found
     * @throws CmdLineException When required command value is missing
     * @throws CmdLineException When option format is invalid
     * @throws CmdLineException When required argument is missing
     */
    private function setUpArgsMap(): void
    {
        $args = $GLOBALS['argv'];
        $startIndex = array_search($this->getAlias(), $args);

        if ($startIndex === false) {
            throw CmdLineException::aliasNotFound($this->getAlias());
        } else {
            $startIndex++;
        }

        $shellArgs = [];

        if (!isset($args[$startIndex]) || ($args[$startIndex][0] === '-')) {
            if ($this->isCommandValueRequired()) {
                throw CmdLineException::missingRequiredValue($this->getAlias());
            }
        } else {
            $this->argsMap[$this->getAlias()] = $args[$startIndex];
            $startIndex++;
        }

        for ($i = $startIndex; $i < count($args); $i++) {
            $arg = $args[$i];

            // NOTE: Store "name" as a key of the hashmap, if it is a long option (--name), or "n" if it is a short option (-n)
            if ($arg[0] . $arg[1] === '--') {
                $key = substr($arg, 2);
            } elseif ($arg[0] === '-') {
                $key = substr($arg, 1);
            } else {
                throw CmdLineException::invalidOptionFormat();
            }

            $shellArgs[$key] = true;

            if (isset($args[$i + 1]) && $args[$i + 1] !== '-') {
                $shellArgs[$key] = $args[$i + 1];
                $i++;
            }
        }

        foreach ($this->getArguments() as $argument) {
            $argString = $argument->getArgument();
            $value = null;

            if ($argument->isShortAllowed() && isset($shellArgs[$argString[0]])) {
                $value = $shellArgs[$argString[0]];
            } elseif (isset($shellArgs[$argString])) {
                $value = $shellArgs[$argString];
            }

            if ($value === null) {
                if ($argument->isRequired()) {
                    throw CmdLineException::missingRequiredArgument($argString);
                } else {
                    $this->argsMap[$argString] = false;
                }
            } else {
                $this->argsMap[$argString] = $value;
            }
        }

        $this->log(json_encode($this->argsMap));
    }

    public static function getHelp(): string
    {
        $commandName = static::getAlias();
        $value = static::isCommandValueRequired() ? ' <value>' : '';

        $helpString = sprintf("Command: %s%s\n\n", $commandName, $value);
        $helpString .= sprintf("Description:\n  %s\n", static::getDescription());

        // Why: code-gen command needs command values
        if (static::isCommandValueRequired()) {
            $helpString .= "\nValues:\n";
            foreach (static::getCommandValues() as $value => $description) {
                $helpString .= sprintf("  %-12s %s\n", $value, $description);
            }
        }

        $arguments = static::getArguments();
        if (!empty($arguments)) {
            $helpString .= "\nArguments:\n";
            foreach ($arguments as $argument) {
                $helpString .= sprintf(
                    "  --%s%s: %s%s\n",
                    $argument->getArgument(),
                    $argument->isShortAllowed() ? ' (-' . $argument->getArgument()[0] . ')' : '',
                    $argument->getDescription(),
                    $argument->isRequired() ? ' (Required)' : ' (Optional)'
                );
            }
        }

        $helpString .= "\nExamples:\n" . static::getExamples() . PHP_EOL;

        return $helpString;
    }

    public static function getAlias(): string
    {
        return static::$alias !== null ? static::$alias : static::class;
    }

    public static function isCommandValueRequired(): bool
    {
        return static::$requiredCommandValue;
    }

    public function getCommandValue(): string
    {
        return $this->argsMap[static::getAlias()] ?? '';
    }

    public function getArgumentValue(string $arg): bool|string
    {
        return $this->argsMap[$arg];
    }

    protected function log(string $info): void
    {
        fwrite(STDOUT, $info . PHP_EOL);
    }

    abstract protected static function getDescription(): string;

    abstract protected static function getExamples(): string;

    abstract public static function getArguments(): array;

    abstract public function execute(): int;
}
