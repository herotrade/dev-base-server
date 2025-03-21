<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/jwt
 *
 * @link     https://github.com/hyperf-ext/jwt
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/jwt/blob/master/LICENSE
 */

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class JwtSecretCommand extends HyperfCommand
{
    protected ?string $name = 'jwt:secret';

    protected string $description = '设置用于签署令牌的JWT密钥';

    public function configure()
    {
        parent::configure();
        $this->setDescription($this->description);
        $this->addOption('show', 's', InputOption::VALUE_NONE, '生成并显示密钥而不是修改文件');
        $this->addOption('always-no', null, InputOption::VALUE_NONE, '跳过生成密钥如果它已经存在');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, '跳过确认覆盖现有密钥');
    }

    public function handle()
    {
        // # 管理端jwt密钥 jwt密钥修改，命令：php bin/hyperf jwt:secret
        // JWT_SECRET=azOVxsOWt3r0ozZNz***************hcQi2ByXa/2A==

        // # 用户端jwt密钥 jwt密钥修改，命令：php bin/hyperf jwt:secret
        // JWT_API_SECRET=azOVxsOWt3r0ozZNz***************hcQi2ByXa/2A==

        // 询问是管理端还是用户端
        $type = $this->choice('请选择JWT密钥类型', ['管理端', '用户端']);

        $key = base64_encode(random_bytes(64));

        if ($this->getOption('show')) {
            $this->comment($key);
            return;
        }

        if (file_exists($path = $this->envFilePath()) === false) {
            $this->displayKey($key, $type);
            return;
        }

        if ($type == '管理端') {
            if (str_contains(file_get_contents($path), 'JWT_SECRET') === false) {
                file_put_contents($path, "\nJWT_SECRET={$key}\n", FILE_APPEND);
            } else {
                if ($this->getOption('always-no')) {
                    $this->comment('密钥已存在。跳过...');
                    return;
                }

                if ($this->isConfirmed() === false) {
                    $this->comment('没有更改密钥。');
                    return;
                }

                file_put_contents($path, preg_replace(
                    "~JWT_SECRET=[^\n]*~",
                    "JWT_SECRET=\"{$key}\"",
                    file_get_contents($path)
                ));
            }
        } else if ($type == '用户端') {
            if (str_contains(file_get_contents($path), 'JWT_API_SECRET') === false) {
                file_put_contents($path, "\nJWT_API_SECRET={$key}\n", FILE_APPEND);
            } else {
                if ($this->getOption('always-no')) {
                    $this->comment('密钥已存在。跳过...');
                    return;
                }

                if ($this->isConfirmed() === false) {
                    $this->comment('没有更改密钥。');
                    return;
                }

                file_put_contents($path, preg_replace(
                    "~JWT_API_SECRET=[^\n]*~",
                    "JWT_API_SECRET=\"{$key}\"",
                    file_get_contents($path)
                ));
            }
        } else {
            $this->comment('输入错误，请重新输入');
            return;
        }

        $this->displayKey($key, $type);
    }

    protected function displayKey(string $key, string $type): void
    {
        $this->info("【{$type}】JWT密钥 [<comment>{$key}</comment>] (base64编码)设置成功。");
    }

    protected function isConfirmed(): bool
    {
        return $this->getOption('force') ? true : $this->confirm(
            '确定要覆盖密钥吗？这将使所有现有令牌失效。'
        );
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

    protected function envFilePath(): string
    {
        return BASE_PATH . '/.env';
    }
}
