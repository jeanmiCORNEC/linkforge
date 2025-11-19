<?php

namespace App\Support;

class CsvExporter
{
    /**
     * Build a CSV string using provided columns and rows.
     *
     * @param  array<string>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     */
    public static function build(array $columns, array $rows): string
    {
        $escape = static fn ($value) => '"' . str_replace('"', '""', (string) $value) . '"';

        $lines   = [];
        $lines[] = implode(',', array_map($escape, $columns));

        foreach ($rows as $row) {
            $ordered = [];

            foreach ($columns as $column) {
                $ordered[] = $row[$column] ?? '';
            }

            $lines[] = implode(',', array_map($escape, $ordered));
        }

        return implode("\n", $lines);
    }
}
