<?php

declare(strict_types=1);

namespace App\Command\Tools;

use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class RequestCommand extends GeneratorCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('x:request');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('创建一个 Request，默认包含不同请求方式对应的验证器及一些常用的验证规则示例');

        $this->addArgument('name', InputArgument::REQUIRED, '验证器名称，请携带模块名称');
        $this->addOption('crud', 'c', InputOption::VALUE_NONE, '是否为实现 curd 的控制器的验证器，如果是则自动生成各接口的验证规则');
        $this->addOption('model_namespace', 'mn', InputOption::VALUE_OPTIONAL, '模型命名空间，设置已存在的模型的命名空间（如：App\\Model\\Strategy 或者 App/Model/Strategy）。');
        $this->addOption('model', 'm', InputOption::VALUE_OPTIONAL, '当为实现 curd 的 Service 时，需要设置操作的模型名称，默认引入对应模块下的 Model 空间下的模型');
        $this->addOption('module_model', 'mm', InputOption::VALUE_NONE, '模型是否位于模块下');
    }

    public function handle()
    {
        if ($this->input->getOption('crud') && empty($this->input->getOption('model'))) {
            // 抛出异常
            throw new \Exception('请设置操作的模型名称，如：-m User');
        }
    }

    protected function getStub(): string
    {
        if (str_starts_with($this->input->getArgument('name'), 'Admin')) {
            if ($this->input->getOption('crud')) {
                return __DIR__ . '/stubs/admin/request-curd.stub';
            }
            return __DIR__ . '/stubs/admin/request.stub';
        }
        if ($this->input->getOption('crud')) {
            return __DIR__ . '/stubs/request-curd.stub';
        }
        return __DIR__ . '/stubs/request.stub';
    }

    protected function getDefaultNamespace(): string
    {
        $name = $this->input->getArgument('name');
        if (!str_contains($name, '/')) {
            return $this->getConfig()['namespace'] ?? 'App\Request';
        }

        $name_arr = explode("/", $name);
        $module = array_shift($name_arr);
        $namespace = 'App\\Http\\' . $module . '\\Request';
        return $namespace;
    }

    protected function loadStubContent(string $stub_content): string
    {
        if ($this->input->getOption('crud')) {
            $name = $this->input->getArgument('name');
            $module = explode("/", $name)[0];

            // 指定了模型的命名空间
            if ($modelNamespace = $this->input->getOption('model_namespace')) {
                if (str_contains($modelNamespace, '/')) {
                    $modelNamespace = str_replace('/', '\\', $modelNamespace);
                }
                $model_class = $modelNamespace;

                $model = end(explode("\\", $model_class));
            } else {
                $model = $this->input->getOption('model');
                if ($this->input->getOption('module_model')) {
                    $model_class = 'App\\' . $module . '\\Model\\' . $model;
                } else {
                    $model_class = 'App\\Model\\' . $model;
                }
            }

            $stub_content = str_replace(
                ['%MODELCLASS%', '%MODEL%'],
                [$model_class, $model],
                $stub_content
            );
        }

        return $stub_content;
    }
}
