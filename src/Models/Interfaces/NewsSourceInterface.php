<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Models\User;
use Plasticode\Collections\TagLinkCollection;

/**
 * @property string|null $publishedAt
 */
interface NewsSourceInterface extends CreatedAtInterface, LinkableInterface, TaggedInterface, UpdatedAtInterface
{
    public function largeImage(): ?string;
    public function image(): ?string;
    public function video(): ?string;

    public function displayTitle(): string;

    public function hasText(): bool;

    public function fullText(): ?string;
    public function shortText(): ?string;

    public function publishedAtIso(): string;

    public function creator(): ?User;

    public function tagLinks(): TagLinkCollection;
}
