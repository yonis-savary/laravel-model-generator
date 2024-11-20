<?php

namespace YonisSavary\LaravelModelGenerator\Inspectors;

use YonisSavary\LaravelModelGenerator\Descriptors\ColumnDescriptor;
use YonisSavary\LaravelModelGenerator\Descriptors\ColumnDataType;
use YonisSavary\LaravelModelGenerator\Descriptors\TableDescriptor;
use Illuminate\Support\Facades\DB;

class MySQLInspector implements DatabaseInspectorInterface
{
    public function getConnectionType(): string
    {
        return "mysql";
    }

    public function getSingleTableDescription(string $tableName): TableDescriptor
    {
        $schema = DB::select("SHOW CREATE TABLE `$tableName`")[0]->{'Create Table'};

        $arrayDescription = preg_replace("/CREATE.+?\\(|\\)$/", "", $schema);

        $columns = [];
        preg_match_all("/(?:`.+?`|PRIMARY|KEY|CONSTRAINT).+?(?:\n|$)/", $arrayDescription, $columns);
        $columns = $columns[0]; // First "group" (matches)

        $description = new TableDescriptor;
        $description->table = $tableName;
        $description->sqlDescriptionDump = $schema;

        $primaryKeyName = null;

        foreach ($columns as $column)
        {
            $column = trim($column);

            if (preg_match("/PRIMARY KEY \(`(.+?)`\)/", $column, $primaryKey))
            {
                $primaryKeyName = $primaryKey[1];
                continue;
            }

            if (!str_starts_with($column, '`')) // skip foreign key and other statements, only retrieve columns
                continue;

            $column = preg_replace("/,$/", "", $column);

            $fieldName = null;
            preg_match('/`(.+?)`/', $column, $fieldName);
            $fieldName = $fieldName[1];

            $type = str_replace("`$fieldName` ", "", $column);
            $type = preg_replace("/ .+/", "", $type);

            $columnDescription = ColumnDescriptor::fromDescription($column);
            $columnDescription->name = $fieldName;
            $columnDescription->type = ColumnDataType::fromString($type);

            $description->fields[] = $columnDescription;
        }

        if ($primaryKeyName)
        {
            foreach ($description->fields as &$field)
            {
                if ($field->name === $primaryKeyName)
                {
                    $field->isPrimaryKey = true;
                    break;
                }
            }
        }

        return $description;
    }

    public function getTableDescriptors(): array
    {
        return collect(DB::select("SHOW TABLES"))
        ->map(fn($x) => array_values((array) $x)[0])
        ->map(fn($x) => $this->getSingleTableDescription($x))
        ->toArray();
    }
}