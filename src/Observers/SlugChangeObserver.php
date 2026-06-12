<?php

declare(strict_types=1);

namespace Webfloo\Observers;

use Illuminate\Database\Eloquent\Model;
use Webfloo\Models\Redirect;

/**
 * Creates a 301 redirect from the old public URL whenever a slug changes,
 * so inbound links to renamed content keep resolving. Registered for the
 * models exposing a `url` accessor (Page, Post, Project) when the
 * redirects module is enabled.
 */
class SlugChangeObserver
{
    public function updated(Model $model): void
    {
        if (! $model->wasChanged('slug')) {
            return;
        }

        // Reparented in the same save: the old path ran through a parent
        // chain that no longer exists on this instance — a wrong redirect
        // is worse than none, so skip.
        if ($model->wasChanged('parent_id')) {
            return;
        }

        $oldSlug = $model->getOriginal('slug');
        // public_url first: Project's `url` column stores the external
        // client-site link, its public path lives under public_url.
        $newUrl = $model->getAttribute('public_url') ?? $model->getAttribute('url');

        if (! is_string($oldSlug) || $oldSlug === '' || ! is_string($newUrl)) {
            return;
        }

        // The slug is always the last URL segment (nested pages included),
        // so the old URL is the new one with that segment swapped back.
        $segments = explode('/', ltrim($newUrl, '/'));
        $segments[array_key_last($segments)] = $oldSlug;

        $oldPath = Redirect::normalizePath(implode('/', $segments));
        $newPath = Redirect::normalizePath($newUrl);

        if ($oldPath === $newPath) {
            return;
        }

        Redirect::updateOrCreate(
            ['from_path' => $oldPath],
            ['to_path' => $newPath, 'status_code' => 301, 'is_active' => true],
        );

        // Renaming back to a previously-used slug would leave an inverse rule
        // (new -> old) that loops with the one just created once the content
        // is ever unpublished — drop any rule starting at the live URL.
        Redirect::query()->where('from_path', $newPath)->delete();
    }
}
