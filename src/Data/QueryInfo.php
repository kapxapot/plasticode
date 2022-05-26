<?php

namespace Plasticode\Data;

use DateTime;

class QueryInfo
{
    public string $query;
    public string $description;
    public array $params;
    public array $caller;
    public DateTime $time;
}
