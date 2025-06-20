<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Command\Seed;

use Hyperf\Command\Concerns\Confirmable as ConfirmableTrait;
use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Seeders\Seed;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class SeedCommand extends \Hyperf\Database\Commands\Seeders\BaseCommand
{
    use ConfirmableTrait;

    /**
     * Create a new seed command instance.
     */
    public function __construct(protected Seed $seed)
    {
        parent::__construct('x:seed');
        $this->setDescription('重写的 seed 命令，指定 path 后仅运行指定 path 下的 seeders');
    }

    /**
     * Handle the current command.
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->seed->setOutput($this->output);

        if ($this->input->hasOption('database') && $this->input->getOption('database')) {
            $this->seed->setConnection($this->input->getOption('database'));
        }

        $this->seed->run($this->getSeederPaths());
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the seeders file stored'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided seeder file paths are pre-resolved absolute paths'],
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
        ];
    }

    protected function getSeederPaths(): array
    {
        // 当指定了 path 时，则只执行指定路径下的 Seeder
        if ($this->getSeederPath() != BASE_PATH . DIRECTORY_SEPARATOR . 'seeders') {
            return [$this->getSeederPath()];
        }
        return array_merge(
            $this->seed->paths(),
            [$this->getSeederPath()]
        );
    }
}
