<?php

/*
 * This Abstract class controls the construction of the main seeding system through inversion of control, similar to a framework.
 * This class is responsible for validating and inserting rows into the database.
 */

namespace Database;

use Carbon\Carbon;
use Exception;
use InvalidArgumentException;

abstract class AbstractSeeder implements Seeder
{
    protected MySQLWrapper $conn;
    protected ?string $tableName = null;

    // tableColumns is an array of associative arrays containing 'data_type' and 'column_name'.
    protected array $tableColumns = [];

    /*
     * These are the available types of columns. They are used for validation checks and for bind_param().
     * The key is the type as a string and the value is the string for bind_param().
     */
    const AVAILABLE_TYPES = [
        'int' => 'i',
        'float' => 'd',
        'string' => 's',
        'Carbon' => 's',
    ];

    public function __construct(MySQLWrapper $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @throws Exception When table name is not set
     * @throws Exception When table columns are not set
     */
    public function seed(): void
    {
        $data = $this->createRowData();

        if ($this->tableName === null) {
            throw new Exception('Class requires a table name');
        }
        if (empty($this->tableColumns)) {
            throw new Exception('Class requires a columns');
        }

        foreach ($data as $row) {
            $this->validateRow($row);
            $this->insertRow($row);
        }
    }

    /**
     * @throws Exception                When row does not match the number of columns in the table
     * @throws InvalidArgumentException When data type is not an available data type
     * @throws InvalidArgumentException When value for column is not of the correct type
     * @throws InvalidArgumentException When value for column is not an instance of Carbon
     */
    protected function validateRow(array $row): void
    {
        if (count($row) !== count($this->tableColumns)) {
            throw new Exception('Row does not match the number of columns in the table');
        }

        foreach ($row as $i => $value) {
            $columnDataType = $this->tableColumns[$i]['data_type'];
            $columnName = $this->tableColumns[$i]['column_name'];

            if (!isset(static::AVAILABLE_TYPES[$columnDataType])) {
                throw new InvalidArgumentException(sprintf('The data type %s is not an available data type.', $columnDataType));
            }

            if ($columnDataType === 'Carbon') {
                // Why: null represents the value never in expiresAt
                if ($value !== null && !$value instanceof Carbon) {
                    throw new InvalidArgumentException(sprintf('Value for %s should be an instance of Carbon or null. Here is the current value: %s', $columnName, json_encode($value)));
                }
                // Why: get_debug_type will return double instead of float, which is suitable in this case
            } elseif (get_debug_type($value) !== $columnDataType) {
                throw new InvalidArgumentException(sprintf('Value for %s should be of type %s. Here is the current value: %s', $columnName, $columnDataType, json_encode($value)));
            }
        }
    }

    /**
     * @throws Exception When failed to prepare statement
     */
    protected function insertRow(array $row): void
    {
        $columnNames = array_map(function ($columnInfo) { return $columnInfo['column_name']; }, $this->tableColumns);
        $placeholders = str_repeat('?,', count($row) - 1) . '?';

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->tableName,
            implode(', ', $columnNames),
            $placeholders
        );

        $this->log('SQL: ' . $sql);

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        // Mysqli will handle null values passed to any of the accepted data types.
        $dataTypes = implode(array_map(function ($columnInfo) { return static::AVAILABLE_TYPES[$columnInfo['data_type']]; }, $this->tableColumns));

        $values = array_map(function ($value) {
            if ($value instanceof Carbon) {
                return $value->toDateTimeString();
            }

            return $value;
        }, $row);

        $stmt->bind_param($dataTypes, ...$values);
        $stmt->execute();

        $this->log('Successfully inserted ' . $stmt->affected_rows . ' row!');
    }

    protected function log(string $info): void
    {
        fwrite(STDOUT, $info . PHP_EOL);
    }
}
