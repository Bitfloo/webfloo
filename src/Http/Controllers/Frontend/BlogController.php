<?php

declare(strict_types=1);

namespace Webfloo\Http\Controllers\Frontend;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Webfloo\Models\Post;
use Webfloo\Models\PostCategory;

class BlogController extends FrontendController
{
    private const PER_PAGE = 12;

    private const MIN_SEARCH_LENGTH = 3;

    public function index(Request $request): View
    {
        $query = Post::query()->published()->with('category')->orderByDesc('published_at');

        $search = trim($request->string('q')->toString());
        if (mb_strlen($search) >= self::MIN_SEARCH_LENGTH) {
            // LIKE on the raw JSON column text works across MySQL/MariaDB/SQLite
            // (searches every locale at once — acceptable for v1 search).
            $query->where(function (Builder $q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $category = $request->string('category')->toString();
        if ($category !== '') {
            $query->whereHas('category', fn (Builder $q): Builder => $q->where('slug', $category));
        }

        return view('webfloo::frontend.blog.index', [
            'posts' => $query->paginate(self::PER_PAGE)->withQueryString(),
            'categories' => PostCategory::query()->orderBy('sort_order')->get(),
            'search' => $search,
        ]);
    }

    public function show(string $slug): View
    {
        /** @var Post|null $post */
        $post = Post::query()->published()->with(['category'])->where('slug', $slug)->first();

        if ($post === null) {
            $this->abortNotFound();
        }

        $post->incrementViews();

        return view('webfloo::frontend.blog.show', ['post' => $post]);
    }
}
