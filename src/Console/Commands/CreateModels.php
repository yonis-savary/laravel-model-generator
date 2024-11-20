<?php

namespace YonisSavary\LaravelModelGenerator\Console\Commands;

use YonisSavary\LaravelModelGenerator\Exceptions\FileWasEditedException;
use YonisSavary\LaravelModelGenerator\Inspectors\DatabaseInspectorInterface;
use YonisSavary\LaravelModelGenerator\Inspectors\MySQLInspector;
use YonisSavary\LaravelModelGenerator\Inspectors\PostgresInspector;
use YonisSavary\LaravelModelGenerator\Inspectors\SQLiteInspector;
use YonisSavary\LaravelModelGenerator\ModelWriter;
use Illuminate\Console\Command;
use Throwable;

class CreateModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:auto-models {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create models in your application from your database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $driver = env("DB_CONNECTION", false))
            $this->fail("Could not retrieve database driver from .env, exiting.");

        $inspector = match($driver){
            "sqlite" => new SQLiteInspector,
            "pgsql"  => new PostgresInspector,
            "mysql"  => new MySQLInspector
        };
        if (!$inspector)
            $this->fail("Unsupported database connection type [$driver], supported are sqlite, pgsql, mysql");
        /** @var DatabaseInspectorInterface $inspector */

        $this->comment("Using [" . $inspector::class . "] inspector");
        if ($useForce = $this->option("force"))
            $this->comment("--force mode is enabled");

        $tables = $inspector->getTableDescriptors();
        $writer = new ModelWriter($useForce);

        $this->info("Inspector provided " . count($tables) . " table descriptions");
        foreach ($tables as $description)
        {
            try
            {
                $filePath = str_replace(base_path() . "/", "",  $writer->writeFileForModel($description));
                $this->info("- File written [$filePath]");
            }
            catch (FileWasEditedException $exception)
            {
                $this->warn("- File " . $exception->file . " was manually edited, use --force to overwrite it");
            }
            catch (Throwable $exception)
            {
                $this->error("- Caught an exception : " . $exception->getMessage());
                report($exception);
            }
        }

    }
}
