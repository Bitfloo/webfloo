<?php

namespace Webfloo\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 *
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_image
 * @property bool|null $no_index
 * @property string|null $title
 * @property string|null $name
 * @property string|null $excerpt
 * @property string|null $description
 * @property string|null $content
 * @property string|null $featured_image
 * @property array<string> $fillable
 * @property array<string, string> $casts
 */
trait HasSeo
{
    public function initializeHasSeo(): void
    {
        $this->fillable = array_merge($this->fillable, [
            'meta_title',
            'meta_description',
            'meta_image',
            'no_index',
        ]);

        $this->casts = array_merge($this->casts, [
            'no_index' => 'boolean',
        ]);
    }

    public function getSeoTitle(): string
    {
        /** @var mixed $metaTitle */
        $metaTitle = $this->meta_title;
        if (is_string($metaTitle) && $metaTitle !== '') {
            return $metaTitle;
        }

        /** @var mixed $title */
        $title = $this->title;
        if (is_string($title) && $title !== '') {
            return $title;
        }

        /** @var mixed $name */
        $name = $this->name;
        if (is_string($name) && $name !== '') {
            return $name;
        }

        $siteName = setting('site_name', config('app.name'));

        return is_string($siteName) ? $siteName : (is_string(config('app.name')) ? config('app.name') : 'Site');
    }

    public function getSeoDescription(): ?string
    {
        $metaDescription = $this->meta_description;
        if (is_string($metaDescription) && $metaDescription !== '') {
            return $metaDescription;
        }

        $content = $this->excerpt ?? $this->description ?? $this->content ?? null;

        if (is_string($content) && $content !== '') {
            return Str::limit(strip_tags($content), 160);
        }

        $siteDescription = setting('site_description');

        return is_string($siteDescription) ? $siteDescription : null;
    }

    public function getSeoImage(): ?string
    {
        if ($this->meta_image !== null && $this->meta_image !== '') {
            return $this->meta_image;
        }

        if ($this->featured_image !== null && $this->featured_image !== '') {
            return $this->featured_image;
        }

        $defaultImage = setting('default_og_image');

        return is_string($defaultImage) ? $defaultImage : null;
    }

    /**
     * @return array{title: string, description: string|null, image: string|null, no_index: bool}
     */
    public function getSeoData(): array
    {
        return [
            'title' => $this->getSeoTitle(),
            'description' => $this->getSeoDescription(),
            'image' => $this->getSeoImage(),
            'no_index' => $this->no_index ?? false,
        ];
    }
}
