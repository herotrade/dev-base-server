<?php

declare(strict_types=1);

namespace App\Command\Tools;

use Hyperf\Command\Annotation\Command;
use Hyperf\Stringable\StrCache;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class ServiceCommand extends GeneratorCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('x:service');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('创建一个 Service，当指定模型时将自动生成对应模型的增、删、改的方法');

        $this->addArgument('name', InputArgument::REQUIRED, 'Service 名称，请携带模块名称');
        $this->addOption('request', 'r', InputOption::VALUE_NONE, '是否自动导入 Request 类');
        $this->addOption('crud', 'c', InputOption::VALUE_NONE, '是否为实现 curd 的控制器的 Service，如果是则自动生成增、删、改对应的方法');
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
        if ($this->input->getOption('crud')) {
            return __DIR__ . '/stubs/service-curd.stub';
        }
        return __DIR__ . '/stubs/service.stub';
    }

    protected function getDefaultNamespace(): string
    {
        $name = $this->input->getArgument('name');
        if (!str_contains($name, '/')) {
            return $this->getConfig()['namespace'] ?? 'App\Service';
        }

        $name_arr = explode("/", $name);
        $module = array_shift($name_arr);
        $namespace = 'App\\Http\\' . $module . '\\Service';
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
            $model_snake_name = StrCache::snake($model);
            $stub_content = str_replace(
                ['%MODELCLASS%', '%MODEL%', '%MODEL_SNAKE_NAME%'],
                [$model_class, $model, $model_snake_name],
                $stub_content
            );
        }

        if ($this->input->getOption('request')) {
            // Request
            $r_name = $this->input->getOption('request');
            $name_arr = explode("/", $r_name);
            $module = array_shift($name_arr);
            $r_namespace_class = 'App\\Http\\' . $module . '\\Request' . '\\' . implode("\\", $name_arr);
            $request = end($name_arr);
            $request_class = $r_namespace_class;

            $stub_content = str_replace(
                ['%REQUESTCLASS%', '%REQUEST%'],
                [$request_class, $request],
                $stub_content
            );
        }

        return $stub_content;
    }
}
