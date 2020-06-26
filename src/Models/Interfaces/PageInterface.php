<?php

namespace Plasticode\Models\Interfaces;

interface PageInterface extends NewsSourceInterface
{
    function isPublished() : bool;
    function getSlug() : string;
}
