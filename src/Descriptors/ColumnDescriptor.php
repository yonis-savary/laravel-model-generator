<?php

namespace YonisSavary\LaravelModelGenerator\Descriptors;

class ColumnDescriptor
{

    public string $name = "id";
    public ColumnDataType $type = ColumnDataType::INTEGER;
    public bool $unique = false;
    public bool $nullable = true;
    public bool $isPrimaryKey = false;
    public bool $autoincrement = false;
    public mixed $default = null;
    public bool $isGenerated = false;


    public function getDefaultPHPVariant(): string|false
    {
        $default = $this->default ?? null;
        $defaultValue = null;

        if ($this->isGenerated || $this->autoincrement)
            return false;

        if ($default === null || strtolower($default) == 'null')
            return 'null';

        if (!preg_match("/^(`'\")(.+?)\$1$/", $default, $defaultValue))
            return false;

        $defaultValue = $defaultValue[2];

        return match(strtolower($defaultValue)) {
            'null' => 'null',
            'false' => 'false',
            'true' => 'true',
            default => '"' . $defaultValue . '"'
        };
    }

    public static function fromDescription(string $sqlDescription): ColumnDescriptor
    {
        // Try to delete table name to avoid confusion
        $sqlDescription = preg_replace("/(`'\").+?\$1/", "", $sqlDescription);
        $safeDescription = strtolower($sqlDescription);

        $field = new ColumnDescriptor;

        if (str_contains($safeDescription, "not null"))
            $field->nullable = false;
        else if (str_contains($safeDescription, "null"))
            $field->nullable = true;

        if (str_contains($safeDescription, "primary key"))
            $field->isPrimaryKey = true;

        if (preg_match("/auto_?increment/i", $safeDescription))
            $field->autoincrement = true;

        $defaultValue = null;
        if (preg_match("/default (.+?) ?$/i", $sqlDescription, $defaultValue))
            $field->default = $defaultValue[1];

        return $field;
    }

    public function isFillable(): bool
    {
        return !($this->isGenerated || $this->isPrimaryKey);
    }

    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "type" => ColumnDataType::toString($this->type),
            "unique" => $this->unique,
            "nullable" => $this->nullable,
            "isPrimaryKey" => $this->isPrimaryKey,
            "autoincrement" => $this->autoincrement,
            "default" => $this->default,
        ];
    }
}