<?php

namespace Plasticode\Repositories\Interfaces;

use Plasticode\Models\TelegramUser;
use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface TelegramUserRepositoryInterface extends ChangingRepositoryInterface
{
    public function get(?int $id): ?TelegramUser;

    public function getByTelegramId(int $id): ?TelegramUser;

    public function getByUser(User $user): ?TelegramUser;

    public function save(TelegramUser $user): TelegramUser;

    public function store(array $data): TelegramUser;
}
