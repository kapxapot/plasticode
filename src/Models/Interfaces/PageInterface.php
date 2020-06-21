<?php

namespace Plasticode\Models\Interfaces;

interface PageInterface extends DbModelInterface
{
    function isPublished() : bool;
    function getSlug() : string;
}
