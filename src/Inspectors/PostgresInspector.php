<?php

namespace YonisSavary\LaravelModelGenerator\Inspectors;

use YonisSavary\LaravelModelGenerator\Descriptors\ColumnDescriptor;
use YonisSavary\LaravelModelGenerator\Descriptors\ColumnDataType;
use YonisSavary\LaravelModelGenerator\Descriptors\TableDescriptor;
use Illuminate\Support\Facades\DB;

class PostgresInspector implements DatabaseInspectorInterface
{
    public function getConnectionType(): string
    {
        return "pgsql";
    }

    public function getSingleTableDescription(string $tableName): TableDescriptor
    {
        $description = new TableDescriptor;
        $description->table = $tableName;

        $fields = DB::select(
            "SELECT
                columns.column_name,
                columns.column_default,
                columns.data_type,
                columns.is_nullable,
                columns.ordinal_position,
                columns.character_maximum_length,
                columns.is_generated,
                string_agg(table_constraints.constraint_type, ', ') as constraints

            FROM information_schema.columns

            LEFT JOIN information_schema.constraint_column_usage ON (
                columns.table_catalog = constraint_column_usage.table_catalog AND
                columns.table_schema = constraint_column_usage.table_schema AND
                columns.table_name = constraint_column_usage.table_name AND
                columns.column_name = constraint_column_usage.column_name
            )

            LEFT JOIN information_schema.table_constraints ON (
                constraint_column_usage.table_catalog = table_constraints.constraint_catalog AND
                constraint_column_usage.table_schema = table_constraints.table_schema AND
                constraint_column_usage.constraint_name = table_constraints.constraint_name
            )

            WHERE columns.table_catalog = :schema
            AND columns.table_name = :table

            GROUP BY columns.column_name,
                columns.column_default,
                columns.data_type,
                columns.is_nullable,
                columns.ordinal_position,
                columns.character_maximum_length,
                columns.is_generated
            ORDER BY columns.ordinal_position ASC

        ", ["schema" => env("DB_DATABASE"), "table" => $tableName]);

        $tableSchema = "";

        foreach ($fields as $field)
        {
            $tableSchema .= print_r($field, true) . "\n";

            $columnDescription = new ColumnDescriptor;

            $columnDescription->name = $field->column_name;
            $columnDescription->default = $field->column_default;
            $columnDescription->type = ColumnDataType::fromString($field->data_type) ?? ColumnDataType::VARCHAR;
            $columnDescription->nullable = $field->is_nullable === "YES";
            $columnDescription->isPrimaryKey = str_contains($field->constraints, "PRIMARY KEY");
            $columnDescription->isGenerated = $field->is_generated !== "NEVER";
            $columnDescription->autoincrement = str_contains($field->column_default, "nextval");

            $description->fields[] = $columnDescription;

        }

        $description->sqlDescriptionDump = $tableSchema;

        return $description;
    }

    public function getTableDescriptors(): array
    {
        return collect(DB::select(
            "SELECT table_name
            FROM information_schema.tables
            WHERE table_catalog = :schema
            AND table_type = 'BASE TABLE'
            AND table_schema = 'public'
        ", ["schema" => env("DB_DATABASE")]))
        ->map(fn($x) => $x->table_name)
        ->map(fn($tableName) => $this->getSingleTableDescription($tableName))
        ->toArray();

        return [];
    }
}