<?php

namespace YonisSavary\LaravelModelGenerator\Exceptions;

use Exception;

class FileWasEditedException extends Exception
{
    public function __construct(
        public string $file
    )
    { parent::__construct("File $file was manually edited"); }
}