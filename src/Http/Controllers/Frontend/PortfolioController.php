<?php

declare(strict_types=1);

namespace Webfloo\Http\Controllers\Frontend;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Webfloo\Models\Project;

class PortfolioController extends Controller
{
    public function index(): View
    {
        return view('webfloo::frontend.portfolio.index', [
            'projects' => Project::query()->active()->ordered()->get(),
        ]);
    }

    public function show(string $slug): View
    {
        /** @var Project $project */
        $project = Project::query()->active()->where('slug', $slug)->firstOrFail();

        // Project does not use HasSeo — build the seo array from its fields.
        return view('webfloo::frontend.portfolio.show', [
            'project' => $project,
            'seo' => [
                'title' => $project->title,
                'description' => $project->excerpt,
                'image' => $project->image ? Storage::url($project->image) : null,
                'no_index' => false,
            ],
        ]);
    }
}
