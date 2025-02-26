<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 
 */

namespace App\Service\Permission;

use App\Repository\Permission\MenuRepository;
use App\Service\IService;

final class MenuService extends IService
{
    public function __construct(
        protected readonly MenuRepository $repository
    ) {}

    public function getRepository(): MenuRepository
    {
        return $this->repository;
    }

    public function create(array $data): mixed
    {
        $model = parent::create($data);
        if ($model && $data['meta']['type'] === 'M' && ! empty($data['btnPermission'])) {
            foreach ($data['btnPermission'] as $item) {
                $this->repository->create([
                    'pid' => $model->id,
                    'name' => $item['code'],
                    'sort' => 0,
                    'status' => 1,
                    'meta' => [
                        'title' => $item['title'],
                        'i18n' => $item['i18n'],
                        'type' => 'B',
                    ],
                ]);
            }
        }
        return $model;
    }

    public function updateById(mixed $id, array $data): mixed
    {
        $model = parent::updateById($id, $data);
        if ($model && $data['meta']['type'] === 'M' && ! empty($data['btnPermission'])) {
            foreach ($data['btnPermission'] as $item) {
                if (! empty($item['type']) && $item['type'] === 'B') {
                    $data = [
                        'name' => $item['code'],
                        'meta' => [
                            'title' => $item['title'],
                            'i18n' => $item['i18n'],
                            'type' => 'B',
                        ],
                    ];
                    if (! empty($item['id'])) {
                        $this->repository->updateById($item['id'], $data);
                    } else {
                        $data['parent_id'] = $id;
                        $this->repository->create($data);
                    }
                }
            }
        }
        return $model;
    }
}
