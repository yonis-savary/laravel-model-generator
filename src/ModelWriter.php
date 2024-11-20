<?php

namespace YonisSavary\LaravelModelGenerator;

use YonisSavary\LaravelModelGenerator\Descriptors\TableDescriptor;
use YonisSavary\LaravelModelGenerator\Exceptions\FileWasEditedException;

class ModelWriter
{
    public function __construct(
        protected bool $forceWriting=false
    ){}

    public function getPascalCaseOf(string $string)
    {
        return ucfirst(preg_replace_callback(
            "/_(\w)/",
            fn($m) => strtoupper($m[1]),
            $string
        ));
    }


    public function writeFileForModel(TableDescriptor $description): string
    {
        $modelClassname = $this->getPascalCaseOf($description->table);
        $baseFilename = "$modelClassname.php";
        $modelFilepath = app_path("Models/$baseFilename");

        $maker = new LaravelModelMaker($description, $modelClassname);

        if (
            file_exists($modelFilepath) &&
            LaravelModelMaker::fileWasEdited($modelFilepath) &&
            (!$this->forceWriting)
        )
            throw new FileWasEditedException($modelFilepath);

        file_put_contents($modelFilepath, $maker->getFullContent());
        return $modelFilepath;
    }
}