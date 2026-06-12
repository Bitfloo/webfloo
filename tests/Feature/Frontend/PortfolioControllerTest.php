<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Frontend;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Project;
use Webfloo\Tests\TestCase;

class PortfolioControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('webfloo.features.frontend', true);
    }

    public function test_portfolio_index_lists_active_projects(): void
    {
        Project::factory()->create(['title' => 'Visible project', 'is_active' => true]);
        Project::factory()->create(['title' => 'Hidden inactive project', 'is_active' => false]);

        $this->get('/portfolio')
            ->assertOk()
            ->assertSee('Visible project')
            ->assertDontSee('Hidden inactive project');
    }

    public function test_portfolio_show_renders_active_project(): void
    {
        Project::factory()->create([
            'title' => 'Case study X',
            'slug' => 'case-study-x',
            'is_active' => true,
        ]);

        $this->get('/portfolio/case-study-x')
            ->assertOk()
            ->assertSee('Case study X');
    }

    public function test_portfolio_show_returns_404_for_inactive_project(): void
    {
        Project::factory()->create(['slug' => 'gone-project', 'is_active' => false]);

        $this->get('/portfolio/gone-project')->assertNotFound();
    }
}
