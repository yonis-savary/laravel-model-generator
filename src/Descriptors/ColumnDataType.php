<?php

namespace YonisSavary\LaravelModelGenerator\Descriptors;

enum ColumnDataType
{
    public static function fromString(string $type): ?ColumnDataType
    {
        $type = strtolower($type);

        $keywords = [
            "varchar"           => ColumnDataType::VARCHAR,
            "character varying" => ColumnDataType::VARCHAR,
            "text"              => ColumnDataType::TEXT,
            "decimal"           => ColumnDataType::DECIMAL,
            "bigint"            => ColumnDataType::LONG,
            "int"               => ColumnDataType::INTEGER,
            "long"              => ColumnDataType::LONG,
            "float"             => ColumnDataType::FLOAT,
            "double"            => ColumnDataType::DOUBLE,
            "datetime"          => ColumnDataType::DATETIME,
            "date"              => ColumnDataType::DATE,
            "timestamp"         => ColumnDataType::TIMESTAMP,
        ];

        foreach ($keywords as $keyword => $enumType)
        {
            if (str_contains($type, $keyword))
                return $enumType;
        }

        return null;
    }

    public static function toString(?ColumnDataType $type): string
    {
        return match($type) {
            ColumnDataType::UUID => "string",
            ColumnDataType::INTEGER => "int",
            ColumnDataType::LONG => "int",
            ColumnDataType::FLOAT => "float",
            ColumnDataType::DOUBLE => "float",
            ColumnDataType::DECIMAL => "string",
            ColumnDataType::BOOLEAN => "bool",
            ColumnDataType::VARCHAR => "string",
            ColumnDataType::TEXT => "string",
            ColumnDataType::DATE => "date",
            ColumnDataType::DATETIME => "datetime",
            ColumnDataType::TIMESTAMP => "timestamp",
            null => "string"
        };
    }

    case UUID;

    case INTEGER;
    case LONG;
    case FLOAT;
    case DOUBLE;
    case DECIMAL;

    case BOOLEAN;

    case VARCHAR;
    case TEXT;

    case DATE;
    case DATETIME;
    case TIMESTAMP;
}