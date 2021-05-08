<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Models\TelegramUser;

class TelegramUserCollection extends DbModelCollection
{
    protected string $class = TelegramUser::class;
}
