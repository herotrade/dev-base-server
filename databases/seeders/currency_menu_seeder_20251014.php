<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 */

use App\Model\Permission\Menu;
use App\Model\Permission\Meta;
use Hyperf\Database\Seeders\Seeder;
use Hyperf\DbConnection\Db;

class CurrencyMenuSeeder20251014 extends Seeder
{
    public const BASE_DATA = [
        'name' => '',
        'path' => '',
        'component' => '',
        'redirect' => '',
        'created_by' => 0,
        'updated_by' => 0,
        'remark' => '',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('DB_DRIVER') === 'odbc-sql-server') {
            Db::unprepared('SET IDENTITY_INSERT [' . Menu::getModel()->getTable() . '] ON;');
        }
        $this->create($this->data());
        if (env('DB_DRIVER') === 'odbc-sql-server') {
            Db::unprepared('SET IDENTITY_INSERT [' . Menu::getModel()->getTable() . '] OFF;');
        }
    }

    /**
     * Database seeds data.
     */
    public function data(): array
    {
        return [
            [
                'name' => 'currency',
                'path' => '/currency',
                'component' => 'strategy/views/currency/index',
                'meta' => new Meta([
                    'title' => '交易对',
                    'icon' => 'ant-design:copyright-circle-outlined',
                    'type' => 'M',
                    'hidden' => 0,
                    'componentPath' => 'modules/',
                    'componentSuffix' => '.vue',
                    'breadcrumbEnable' => 1,
                    'copyright' => 1,
                    'cache' => 1,
                    'affix' => 0,
                ]),
                'children' => [
                    [
                        'name' => 'currency:list',
                        'meta' => new Meta([
                            'title' => '交易对列表',
                            'type' => 'B',
                            'i18n' => '',
                        ]),
                    ],
                    [
                        'name' => 'currency:create',
                        'meta' => new Meta([
                            'title' => '交易对添加',
                            'type' => 'B',
                            'i18n' => '',
                        ]),
                    ],
                    [
                        'name' => 'currency:update',
                        'meta' => new Meta([
                            'title' => '交易对更新',
                            'type' => 'B',
                            'i18n' => '',
                        ]),
                    ],
                    [
                        'name' => 'currency:delete',
                        'meta' => new Meta([
                            'title' => '交易对删除',
                            'type' => 'B',
                            'i18n' => '',
                        ]),
                    ],
                ],
            ],
        ];
    }

    public function create(array $data, int $parent_id = 0): void
    {
        foreach ($data as $v) {
            $_v = $v;
            if (isset($v['children'])) {
                unset($_v['children']);
            }
            $_v['parent_id'] = $parent_id;
            $menu = Menu::create(array_merge(self::BASE_DATA, $_v));
            if (isset($v['children']) && count($v['children'])) {
                $this->create($v['children'], $menu->id);
            }
        }
    }
}
