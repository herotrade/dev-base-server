<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 
 */

namespace App\Http;

use App\Model\Permission\Role;
use App\Model\Permission\User;
use App\Service\PassportService;
use App\Service\Permission\UserService;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Lcobucci\JWT\Token\RegisteredClaims;
use Mine\Jwt\Traits\RequestScopedTokenTrait;

final class CurrentUser
{
    use RequestScopedTokenTrait;

    public function __construct(
        private readonly PassportService $service,
        private readonly UserService $userService
    ) {}

    public function user(): ?User
    {
        return $this->userService->getInfo($this->id());
    }

    public function refresh(): array
    {
        return $this->service->refreshToken($this->getToken());
    }

    public function id(): int
    {
        return (int) $this->getToken()->claims()->get(RegisteredClaims::ID);
    }

    public function isSuperAdmin(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function filterCurrentUser(?array $menuTreeList = null, ?array $permissions = null): array
    {
        $permissions ??= $this->user()->getPermissions()->pluck('name')->toArray();
        $menuTreeList ??= $this->globalMenuTreeList()->toArray();

        return array_values(Arr::where(
            array_map(
                fn (array $menu) => $this->filterMenu($menu, $permissions),
                $menuTreeList
            ),
            static fn (array $menu) => \in_array($menu['name'], $permissions, true)
        ));
    }

    public function globalMenuTreeList(): Collection
    {
        // @phpstan-ignore-next-line
        return $this->user()->roles()->get()->map(static function (Role $role) {
            return $role->menus()->where('parent_id', 0)->with('children')->orderBy('sort')->get();
        })->flatten();
    }

    private function filterMenu(array $menu, array $permissions): array
    {
        if (! empty($menu['children'])) {
            $menu['children'] = $this->filterCurrentUser($menu['children'], $permissions);
        }
        return $menu;
    }
}
