<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Models\User;
use Plasticode\Collections\TagLinkCollection;

interface NewsSourceInterface extends LinkableInterface, TaggedInterface, UpdatedAtInterface
{
    function largeImage() : ?string;
    function image() : ?string;
    function video() : ?string;

    function displayTitle() : string;

    function hasText() : bool;

    function fullText() : ?string;
    function shortText() : ?string;

    function publishedAtIso() : string;

    function creator() : ?User;

    function tagLinks() : TagLinkCollection;
}
