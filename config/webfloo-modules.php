<?php

declare(strict_types=1);
use Webfloo\Console\Commands\GenerateSitemap;
use Webfloo\Console\Commands\SendLeadReminders;
use Webfloo\Filament\Pages\CrmDashboard;
use Webfloo\Filament\Pages\PageSettings\ContactPageSettings;
use Webfloo\Filament\Pages\PageSettings\HomePageSettings;
use Webfloo\Filament\Pages\SiteSettings;
use Webfloo\Filament\Resources\FaqResource;
use Webfloo\Filament\Resources\LeadResource;
use Webfloo\Filament\Resources\LeadTagResource;
use Webfloo\Filament\Resources\MenuItemResource;
use Webfloo\Filament\Resources\NewsletterSubscriberResource;
use Webfloo\Filament\Resources\PageResource;
use Webfloo\Filament\Resources\PostCategoryResource;
use Webfloo\Filament\Resources\PostResource;
use Webfloo\Filament\Resources\ProjectResource;
use Webfloo\Filament\Resources\ServiceResource;
use Webfloo\Filament\Resources\TestimonialResource;
use Webfloo\Filament\Widgets\LeadConversionChart;
use Webfloo\Filament\Widgets\LeadsBySourceChart;
use Webfloo\Filament\Widgets\LeadsByStatusChart;
use Webfloo\Filament\Widgets\LeadStatsOverview;
use Webfloo\Filament\Widgets\UpcomingRemindersWidget;
use Webfloo\Models\Faq;
use Webfloo\Models\Lead;
use Webfloo\Models\LeadActivity;
use Webfloo\Models\LeadReminder;
use Webfloo\Models\LeadTag;
use Webfloo\Models\MenuItem;
use Webfloo\Models\NewsletterSubscriber;
use Webfloo\Models\Page;
use Webfloo\Models\Post;
use Webfloo\Models\PostCategory;
use Webfloo\Models\Project;
use Webfloo\Models\Service;
use Webfloo\Models\Testimonial;

/*
|--------------------------------------------------------------------------
| Webfloo Modules Registry
|--------------------------------------------------------------------------
|
| Logiczne grupowanie Resources / Models / Commands / Migrations na moduły.
| NIE jest to filesystem layout (pakiet zachowuje flat `src/` konwencję
| typową dla Laravel library packages — filament/filament, spatie/*).
|
| Rejestr służy:
|   1. Dokumentacji — host devs widzą co należy do którego domain.
|   2. Feature-flagging — każdy moduł z `feature_flag` jest enable/disable
|      przez `config/webfloo.php` `features.*`.
|   3. Conditional wiring — WebflooServiceProvider + WebflooPanel czytają
|      rejestr żeby skip'nąć commands / Resources dla disabled modules.
|   4. Shield permission grouping — `permissions` lista per moduł.
|
| Każdy moduł:
|   - enabled         — computed runtime: `feature_flag` true (albo always-on).
|   - feature_flag    — nazwa klucza z `webfloo.features.*` (null = always-on).
|   - resources       — FQN Filament Resources należących do modułu.
|   - models          — FQN Eloquent Models.
|   - widgets         — FQN Filament Widgets (optional).
|   - pages           — FQN Filament Pages (non-Resource) optional.
|   - commands        — FQN Console Commands (optional).
|   - migrations      — filename pattern w database/migrations/.
|   - permissions     — lista Shield permissions slug (view_any_*, itd.).
|   - depends_on      — lista innych modułów wymaganych (dependency DAG).
|
| Host override: `php artisan vendor:publish --tag=webfloo-modules`.
*/

return [

    'pages' => [
        'feature_flag' => null,
        'resources' => [
            PageResource::class,
        ],
        'models' => [
            Page::class,
        ],
        'widgets' => [],
        'pages' => [
            SiteSettings::class,
            HomePageSettings::class,
            ContactPageSettings::class,
        ],
        'commands' => [],
        'migrations' => [
            '*_create_pages_table',
            '*_add_published_at_index_to_pages_table',
            '*_change_pages_status_from_enum_to_string',
        ],
        'permissions' => [
            'view_any_page', 'view_page', 'create_page', 'update_page', 'delete_page',
            'view_site_settings', 'view_home_page_settings', 'view_contact_page_settings',
        ],
        'depends_on' => [],
    ],

    'blog' => [
        'feature_flag' => 'blog',
        'resources' => [
            PostResource::class,
            PostCategoryResource::class,
        ],
        'models' => [
            Post::class,
            PostCategory::class,
        ],
        'widgets' => [],
        'pages' => [],
        'commands' => [],
        'migrations' => [
            '*_create_post_categories_table',
            '*_create_posts_table',
            '*_create_post_project_table',
            '*_create_post_related_table',
            '*_make_post_categories_translatable',
        ],
        'permissions' => [
            'view_any_post', 'view_post', 'create_post', 'update_post', 'delete_post',
            'view_any_post_category', 'view_post_category', 'create_post_category', 'update_post_category', 'delete_post_category',
        ],
        'depends_on' => [],
    ],

    'portfolio' => [
        'feature_flag' => 'portfolio',
        'resources' => [
            ProjectResource::class,
        ],
        'models' => [
            Project::class,
        ],
        'widgets' => [],
        'pages' => [],
        'commands' => [],
        'migrations' => [
            '*_create_projects_table',
            '*_add_case_study_fields_to_projects_table',
        ],
        'permissions' => [
            'view_any_project', 'view_project', 'create_project', 'update_project', 'delete_project',
        ],
        'depends_on' => [],
    ],

    'services' => [
        'feature_flag' => 'services',
        'resources' => [
            ServiceResource::class,
        ],
        'models' => [
            Service::class,
        ],
        'widgets' => [],
        'pages' => [],
        'commands' => [],
        'migrations' => [
            '*_create_services_table',
        ],
        'permissions' => [
            'view_any_service', 'view_service', 'create_service', 'update_service', 'delete_service',
        ],
        'depends_on' => [],
    ],

    'testimonials' => [
        'feature_flag' => 'testimonials',
        'resources' => [
            TestimonialResource::class,
        ],
        'models' => [
            Testimonial::class,
        ],
        'widgets' => [],
        'pages' => [],
        'commands' => [],
        'migrations' => [
            '*_create_testimonials_table',
            '*_add_is_featured_to_services_and_testimonials',
        ],
        'permissions' => [
            'view_any_testimonial', 'view_testimonial', 'create_testimonial', 'update_testimonial', 'delete_testimonial',
        ],
        'depends_on' => [],
    ],

    'faq' => [
        'feature_flag' => 'faq',
        'resources' => [
            FaqResource::class,
        ],
        'models' => [
            Faq::class,
        ],
        'widgets' => [],
        'pages' => [],
        'commands' => [],
        'migrations' => [
            '*_create_faqs_table',
            '*_add_icon_to_faqs_table',
        ],
        'permissions' => [
            'view_any_faq', 'view_faq', 'create_faq', 'update_faq', 'delete_faq',
        ],
        'depends_on' => [],
    ],

    'newsletter' => [
        'feature_flag' => 'newsletter',
        'resources' => [
            NewsletterSubscriberResource::class,
        ],
        'models' => [
            NewsletterSubscriber::class,
        ],
        'widgets' => [],
        'pages' => [],
        'commands' => [],
        'migrations' => [
            '*_create_newsletter_subscribers_table',
        ],
        /*
         * PII scope (GDPR). Permissions admin-only — ShieldRolesSeeder
         * celowo NIE nadaje tych permissions editor/viewer rolom. Patrz
         * ShieldRolesSeeder::EDITOR_RESOURCES komentarz.
         */
        'permissions' => [
            'view_any_newsletter_subscriber', 'view_newsletter_subscriber',
            'create_newsletter_subscriber', 'update_newsletter_subscriber', 'delete_newsletter_subscriber',
        ],
        'depends_on' => [],
    ],

    'crm' => [
        'feature_flag' => 'crm',
        'resources' => [
            LeadResource::class,
            LeadTagResource::class,
        ],
        'models' => [
            Lead::class,
            LeadTag::class,
            LeadActivity::class,
            LeadReminder::class,
        ],
        'widgets' => [
            LeadStatsOverview::class,
            LeadConversionChart::class,
            LeadsByStatusChart::class,
            LeadsBySourceChart::class,
            UpcomingRemindersWidget::class,
        ],
        'pages' => [
            CrmDashboard::class,
        ],
        'commands' => [
            SendLeadReminders::class,
        ],
        'migrations' => [
            '*_create_leads_table',
            '*_create_lead_activities_table',
            '*_create_lead_reminders_table',
            '*_create_lead_tags_table',
            '*_add_crm_fields_to_leads_table',
            '*_add_consent_at_to_leads_table',
            '*_add_converted_at_index_to_leads_table',
        ],
        'permissions' => [
            'view_any_lead', 'view_lead', 'create_lead', 'update_lead', 'delete_lead',
            'view_any_lead_tag', 'view_lead_tag', 'create_lead_tag', 'update_lead_tag', 'delete_lead_tag',
            'view_crm_dashboard',
        ],
        'depends_on' => [],
    ],

    'menu' => [
        'feature_flag' => 'menu',
        'resources' => [
            MenuItemResource::class,
        ],
        'models' => [
            MenuItem::class,
        ],
        'widgets' => [],
        'pages' => [],
        'commands' => [],
        'migrations' => [
            '*_create_menu_items_table',
        ],
        'permissions' => [
            'view_any_menu_item', 'view_menu_item', 'create_menu_item', 'update_menu_item', 'delete_menu_item',
        ],
        'depends_on' => [],
    ],

    /*
     * SEO jest cross-cutting concern — nie ma dedicated Resources ani
     * tables. Trait HasSeo aplikuje SEO fields na dowolny Model, Command
     * GenerateSitemap czyta published Post+Project+Page. Rejestrowany
     * jako logical module dla documentation / Shield permission grouping
     * (nawet jeśli permissions tutaj puste — host może dodać custom
     * `view_any_sitemap_config` itd.).
     */
    'seo' => [
        'feature_flag' => null,
        'resources' => [],
        'models' => [],
        'widgets' => [],
        'pages' => [],
        'commands' => [
            GenerateSitemap::class,
        ],
        'migrations' => [],
        'permissions' => [],
        'depends_on' => ['blog', 'portfolio', 'pages'],
    ],

];
