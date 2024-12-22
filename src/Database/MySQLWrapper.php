<?php

namespace Database;

use Helpers\Settings;
use mysqli;

class MySQLWrapper extends mysqli
{
    public function __construct(?string $hostname = null, ?string $username = null, ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null)
    {
        /*
         * Reports an error and throws an exception on connection failure.
         * Set this up before initializing the database connection.
         * To test, enter incorrect information in the .env configuration.
        */
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $hostname = $hostname ?? Settings::env('DATABASE_HOST');
        $username = $username ?? Settings::env('DATABASE_USER');
        $password = $password ?? Settings::env('DATABASE_USER_PASSWORD');
        $database = $database ?? Settings::env('DATABASE_NAME');

        parent::__construct($hostname, $username, $password, $database, $port, $socket);
    }

    /*
     * Retrieve the default database name.
     * Errors are thrown on failure (query returns false or no rows)
     */
    public function getDatabaseName(): string
    {
        return $this->query('SELECT database() AS the_db')->fetch_row()[0];
    }
}
