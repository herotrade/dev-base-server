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

use App\Model\Permission\Role;
use App\Repository\Permission\MenuRepository;
use App\Repository\Permission\RoleRepository;
use App\Service\IService;
use Hyperf\Collection\Collection;

/**
 * @extends IService<Role>
 */
final class RoleService extends IService
{
    public function __construct(
        protected readonly RoleRepository $repository,
        protected readonly MenuRepository $menuRepository
    ) {}

    public function getRolePermission(int $id): Collection
    {
        // @phpstan-ignore-next-line
        return $this->repository->findById($id)->menus()->get();
    }

    public function batchGrantPermissionsForRole(int $id, array $permissionsCode): void
    {
        if (\count($permissionsCode) === 0) {
            // @phpstan-ignore-next-line
            $this->repository->findById($id)->menus()->detach();
            return;
        }
        // @phpstan-ignore-next-line
        $this->repository->findById($id)
            ->menus()
            ->sync(
                $this->menuRepository
                    ->list([
                        'code' => $permissionsCode,
                    ])
                    ->map(static fn ($item) => $item->id)
                    ->toArray()
            );
    }
}
