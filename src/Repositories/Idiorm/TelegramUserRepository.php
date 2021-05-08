<?php

namespace Plasticode\Repositories;

use Plasticode\Models\TelegramUser;
use Plasticode\Models\User;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;
use Plasticode\Repositories\Interfaces\TelegramUserRepositoryInterface;

class TelegramUserRepository extends IdiormRepository implements TelegramUserRepositoryInterface
{
    protected function entityClass(): string
    {
        return TelegramUser::class;
    }

    public function get(?int $id): ?TelegramUser
    {
        return $this->getEntity($id);
    }

    public function getByTelegramId(int $id): ?TelegramUser
    {
        return $this->query()->where('telegram_id', $id)->one();
    }

    public function getByUser(User $user): ?TelegramUser
    {
        return $this
            ->query()
            ->where('user_id', $user->getId())
            ->one();
    }

    public function save(TelegramUser $user): TelegramUser
    {
        return $this->saveEntity($user);
    }

    public function store(array $data): TelegramUser
    {
        return $this->storeEntity($data);
    }
}
