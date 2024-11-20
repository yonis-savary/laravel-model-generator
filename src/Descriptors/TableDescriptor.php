<?php

namespace YonisSavary\LaravelModelGenerator\Descriptors;

class TableDescriptor
{
    public string $table = "undefined";
    public string $sqlDescriptionDump = "no sql dump provided";

    protected function findPrimaryField(): ColumnDescriptor|false
    {
        return collect($this->fields)->first(fn(ColumnDescriptor $x) => $x->isPrimaryKey, false);
    }

    public function primaryKey():  ?string
    {
        return $this->findPrimaryField()->name ?? null;
    }

    public function incrementing(): ?bool
    {
        return $this->findPrimaryField()->autoincrement ?? null;
    }

    public function keyType(): ?string
    {
        if ($field = $this->findPrimaryField())
            return ColumnDataType::toString($field->type);
        return false;
    }

    public function createdAt(): ?string
    {
        $field = collect($this->fields)->first(
            fn(ColumnDescriptor $x) =>
                str_contains(strtolower($x->name), "created")
        , false);
        return $field->name ?? false;
    }

    public function updatedAt(): ?string
    {
        $field = collect($this->fields)->first(
            fn(ColumnDescriptor $x) =>
                in_array($x->type, [ColumnDataType::DATETIME, ColumnDataType::TIMESTAMP]) &&
                preg_match("/update|edit/i", $x->name)
        , false);
        return $field->name ?? false;
    }

    /** @var array<ColumnDescriptor> $fields */
    public array $fields = [];


    public function toArray(): array
    {
        return [
            "table" => $this->table,
            "primaryKey" => $this->primaryKey(),
            "incrementing" => $this->incrementing(),
            "keyType" => $this->keyType(),
            "created_at" => $this->createdAt(),
            "updated_at" => $this->updatedAt(),
            "fields" => collect($this->fields)->map(fn(ColumnDescriptor $x) => $x->toArray())->toArray()
        ];
    }
}