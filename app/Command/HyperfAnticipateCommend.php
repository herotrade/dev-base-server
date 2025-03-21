<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class HyperfAnticipateCommend extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('ls');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf 命令列表，可自动补全要执行的命令');
        $this->addOption('la', 'l', InputOption::VALUE_NONE, '展示详细命令信息');
    }

    public function handle()
    {
        // 获取容器中的 ApplicationInterface 实例
        $application = ApplicationContext::getContainer()->get(ApplicationInterface::class);

        // 获取所有注册的命令
        $commands = $application->all();

        $list = [];
        $show_list = [];
        foreach ($commands as $command) {
            $list[] = $command->getName();
            $show_list[] = [
                $command->getName(),
                $command->getDescription()
            ];
        }

        if (is_null($this->getOption('la'))) {
            $chunk_size = 5;
            $chunks = collect($list)->chunk($chunk_size)->toArray();
            foreach ($chunks as &$chunk) {
                foreach ($chunk as $key => &$hyperf_name) {
                    $hyperf_name = "{$key}.{$hyperf_name}";
                }
                unset($hyperf_name);
            }
            unset($chunk);
            $headers = [];
            for ($i = 0; $i < $chunk_size; $i++) {
                $headers[] = 'Command';
            }
            $this->table(
                $headers,
                $chunks,
                'box'
            );
            $hyperf = $this->anticipate('想要执行的 hyperf 命令（也可以输入编号）（可携带参数，如：describe:routes --path=login; 42 --path=login）', $list);
            if (is_numeric($hyperf) && !empty($list[$hyperf])) {
                $hyperf = $list[$hyperf];
            }
            if (str_contains($hyperf, ' ')) {
                $hyperf_arr = explode(" ", $hyperf);
                if (is_numeric($hyperf_arr[0]) && !empty($list[$hyperf_arr[0]])) {
                    $hyperf_arr[0] = $list[$hyperf_arr[0]];
                    $hyperf = implode(" ", $hyperf_arr);
                }
            }
        } else {
            $this->table(
                ['Command', 'Description'],
                $show_list,
                'box'
            );
            $hyperf = $this->anticipate('想要执行的 hyperf 命令（可携带参数，如：describe:routes --path=login;）', $list);
        }

        $this->info("执行 php bin/hyperf.php {$hyperf} 命令：");
        passthru("php bin/hyperf.php {$hyperf}");
        $this->comment("php bin/hyperf.php {$hyperf}");

        return;
    }

    /**
     * @param null|mixed $default
     *
     * @return null|mixed
     */
    protected function getOption(string $name, $default = null)
    {
        $result = $this->input->getOption($name);
        return empty($result) ? $default : $result;
    }
}
