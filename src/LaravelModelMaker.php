<?php

namespace YonisSavary\LaravelModelGenerator;

use YonisSavary\LaravelModelGenerator\Descriptors\ColumnDescriptor;
use YonisSavary\LaravelModelGenerator\Descriptors\TableDescriptor;
use Illuminate\Database\Eloquent\Model;

class LaravelModelMaker
{
    protected string $phpCode;
    protected string $digest;

    public function __construct(TableDescriptor $description, string $classname)
    {
        $phpCode = join(
            "\n", array_filter([
                '<?php ',
                '',
                'namespace App\Models;',
                '',
                'use ' . Model::class . ';',
                '',
                '/**',
                    collect(explode("\n", $description->sqlDescriptionDump))
                    ->map(fn($x) => " * $x")
                    ->join("\n"),
                ' */',
                "class $classname extends Model",
                '{',


                (($createdAt = $description->createdAt()) ? "    const CREATED_AT = \"$createdAt\";" : null),
                (($editedAt = $description->updatedAt())  ? "    const UPDATED_AT = \"$editedAt\";" : null),
                '',


                '    protected $table = "'. $description->table .'";',

                    (($primary = $description->primaryKey()) ?
                        '    protected $primaryKey = "' . $primary . '";' : null),

                    (($keyType = $description->keyType()) ?
                        '    protected $keyType = "'.$keyType.'";' : null),

                '    protected $incrementing = '. ($description->incrementing() ? 'true': 'false') . ';' ,
                '',
                '    protected $attributes = [',
                        ...collect($description->fields)
                        ->map(fn(ColumnDescriptor $f) =>
                        ($expr = $f->getDefaultPHPVariant()) ?
                                '        "' . $f->name .'" => ' . $expr . ',':
                                ''
                        )
                        ->filter()
                        ->toArray(),
                '    ];',

                "}"
            ]
        , fn($x) => $x !== null));

        $phpCode = preg_replace("/\n{3,}/", "\n\n", $phpCode); // Remove empty section
        $phpCode = preg_replace("/\{\n{2,}/", "{\n", $phpCode); // Remove empty lines after opening brackets
        $phpCode = preg_replace_callback("/(\w+)\s+$/m", fn($m) => $m[1], $phpCode); // Remove trailing spaces

        $this->phpCode = $phpCode;
        $this->digest = md5($this->phpCode);
    }

    public function getDigestLessContent(): string
    {
        return $this->phpCode;
    }

    public function getFullContent(): string
    {
        return $this->phpCode . "\n//DIGEST=". $this->digest . "=";
    }

    public static function fileWasEdited(string $file): bool
    {
        $fileContent = file_get_contents($file);
        $digestRegex = "/\s+\\/\\/DIGEST=(.+?)=/s";

        $fileDigest = null;
        if (!preg_match($digestRegex, $fileContent, $fileDigest))
            return true;

        $fileDigest = $fileDigest[1] ?? false;

        $digestLessFileContent = preg_replace($digestRegex, "", $fileContent);

        return md5($digestLessFileContent) !== $fileDigest;
    }

    public function getDigest(): string
    {
        return $this->digest;
    }
}