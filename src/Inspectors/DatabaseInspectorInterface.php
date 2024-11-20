<?php

namespace YonisSavary\LaravelModelGenerator\Inspectors;

use YonisSavary\LaravelModelGenerator\Descriptors\TableDescriptor;

/**
 * Database Inspectors are "drivers" used to describe your database table
 * Their goal is to convert your database description into TableDescriptor objects
 */
interface DatabaseInspectorInterface
{
    /**
     * Get the supported database connection type
     */
    public function getConnectionType(): string;

    /**
     * @return TableDescriptor table description for a single table in the database
     */
    public function getSingleTableDescription(string $tableName): TableDescriptor;

    /**
     * @return array<TableDescriptor> Array of TableDescriptor describing every tables in the database
     */
    public function getTableDescriptors(): array;
}