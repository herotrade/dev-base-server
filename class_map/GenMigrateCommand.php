<?php
declare(strict_types=1);

namespace Hyperf\Database\Commands\Migrations;

use Hyperf\Database\Migrations\MigrationCreator;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

//重写数据库迁移文件生成
class GenMigrateCommand extends BaseCommand
{
    /**
     * Create a new migration install command instance.
     */
    public function __construct(protected MigrationCreator $creator)
    {
        parent::__construct('gen:migration');
        $this->setDescription('生成数据库迁移文件【class_map 重写版本】');
    }

    public function handle()
    {
        $name = Str::snake(trim($this->input->getArgument('name')));
        $table = $this->input->getOption('table');
        $create = $this->input->getOption('create') ?: false;
        if (! $table && is_string($create)) {
            $table = $create;
            $create = true;
        }
        if (! $table) {
            [$table, $create] = TableGuesser::guess($name);
        }
        $this->writeMigration($name, $table, $create);
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the migration'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['create', null, InputOption::VALUE_OPTIONAL, 'The table to be created'],
            ['table', null, InputOption::VALUE_OPTIONAL, 'The table to migrate'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the migration file should be created'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
        ];
    }

    /**
     * Write the migration file to disk.
     */
    protected function writeMigration(string $name, ?string $table, bool $create): void
    {
        try {
            $file = pathinfo($this->creator->create(
                $name,
                $this->getMigrationPath(),
                $table,
                $create
            ), PATHINFO_FILENAME);
            $this->info("<info>[INFO] Created Migration:</info> {$file}");
        } catch (Throwable $e) {
            $this->error("<error>[ERROR] Created Migration:</error> {$e->getMessage()}");
        }
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        return BASE_PATH . DIRECTORY_SEPARATOR . 'databases'.DIRECTORY_SEPARATOR.'migrations';
//        if (! is_null($targetPath = $this->input->getOption('path'))) {
//            return ! $this->usingRealPath()
//                ? BASE_PATH . '/' . $targetPath
//                : $targetPath;
//        }
//
//        return parent::getMigrationPath();
    }

    /**
     * Determine if the given path(s) are pre-resolved "real" paths.
     */
    protected function usingRealPath(): bool
    {
        return $this->input->hasOption('realpath') && $this->input->getOption('realpath');
    }
}