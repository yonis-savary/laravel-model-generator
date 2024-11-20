<?php

namespace YonisSavary\LaravelModelGenerator\Inspectors;

use YonisSavary\LaravelModelGenerator\Descriptors\ColumnDescriptor;
use YonisSavary\LaravelModelGenerator\Descriptors\ColumnDataType;
use YonisSavary\LaravelModelGenerator\Descriptors\TableDescriptor;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SQLiteInspector implements DatabaseInspectorInterface
{
    public function getConnectionType(): string
    {
        return "sqlite";
    }

    public function getSingleTableDescription(string $tableName): TableDescriptor
    {
        $schema = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name= :table ", ["table" => $tableName]);

        if (! $schema = $schema[0]->sql ?? false )
            throw new RuntimeException("Could not retrieve schema of table [$tableName]");

        $arrayDescription = preg_replace("/CREATE.+?\\(|\\)$/", "", $schema);

        $columns = [];
        preg_match_all("/(?:\".+?\"|foreign).+?(?:, |$)/", $arrayDescription, $columns);
        $columns = $columns[0]; // First "group" (matches)

        $description = new TableDescriptor;
        $description->table = $tableName;
        $description->sqlDescriptionDump = $schema;

        foreach ($columns as $column)
        {
            $column = trim($column);
            if (!str_starts_with($column, '"')) // skip foreign key and other statements, only retrieve columns
                continue;

            $column = preg_replace("/,$/", "", $column);

            $fieldName = null;
            preg_match('/"(.+?)"/', $column, $fieldName);
            $fieldName = $fieldName[1];

            $type = str_replace("\"$fieldName\" ", "", $column);
            $type = preg_replace("/ .+/", "", $type);

            $columnDescription = ColumnDescriptor::fromDescription($column);
            $columnDescription->name = $fieldName;
            $columnDescription->type = ColumnDataType::fromString($type) ?? ColumnDataType::VARCHAR;

            $description->fields[] = $columnDescription;
        }

        return $description;
    }

    public function getTableDescriptors(): array
    {
        return collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name"))
        ->map(fn($x) => $this->getSingleTableDescription($x->name))
        ->toArray();
    }
}