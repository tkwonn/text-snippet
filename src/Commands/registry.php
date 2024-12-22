<?php

/*
 * Contains an array of all command class names.
 * This acts as a registry where commands are registered, and the console reads from these.
 * If a command is not registered, it won't be hooked into the console
 */

return [
    Commands\Programs\CodeGeneration::class,
    Commands\Programs\Migrate::class,
    Commands\Programs\Seed::class,
];
