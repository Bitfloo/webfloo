<?php

namespace Webfloo\Database\Seeders;

use Illuminate\Database\Seeder;
use Webfloo\Models\Post;
use Webfloo\Models\PostCategory;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $categories = [
            [
                'name' => 'Development',
                'slug' => 'development',
                'description' => 'Artykuły o programowaniu, architekturze i najlepszych praktykach.',
                'icon' => 'tabler--code',
                'color' => 'primary',
                'sort_order' => 1,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Porady biznesowe dla firm i startupów.',
                'icon' => 'tabler--briefcase',
                'color' => 'secondary',
                'sort_order' => 2,
            ],
            [
                'name' => 'Tutorial',
                'slug' => 'tutorial',
                'description' => 'Praktyczne poradniki krok po kroku.',
                'icon' => 'tabler--school',
                'color' => 'accent',
                'sort_order' => 3,
            ],
        ];

        foreach ($categories as $category) {
            PostCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $devCategory = PostCategory::where('slug', 'development')->first();
        $bizCategory = PostCategory::where('slug', 'business')->first();
        $tutorialCategory = PostCategory::where('slug', 'tutorial')->first();

        // Create posts
        $posts = [
            [
                'title' => 'Dlaczego Laravel to najlepszy wybór dla Twojego projektu w 2025',
                'slug' => 'dlaczego-laravel-najlepszy-wybor-2025',
                'excerpt' => 'Laravel pozostaje najpopularniejszym frameworkiem PHP nie bez powodu. Poznaj kluczowe zalety, które sprawiają, że jest idealnym wyborem dla projektów każdej skali.',
                'content' => $this->getLaravelContent(),
                'post_category_id' => $devCategory?->id,
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Jak skutecznie wdrożyć system CRM w małej firmie',
                'slug' => 'jak-wdrozyc-crm-mala-firma',
                'excerpt' => 'Wdrożenie CRM nie musi być skomplikowane ani drogie. Przedstawiamy praktyczny przewodnik dla małych i średnich przedsiębiorstw.',
                'content' => $this->getCrmContent(),
                'post_category_id' => $bizCategory?->id,
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'is_featured' => false,
                'sort_order' => 2,
            ],
            [
                'title' => 'Filament v5 - Budowanie panelu admina w 30 minut',
                'slug' => 'filament-v5-panel-admina-30-minut',
                'excerpt' => 'Praktyczny tutorial pokazujący, jak stworzyć w pełni funkcjonalny panel administracyjny używając Filament v5 i Laravel.',
                'content' => $this->getFilamentContent(),
                'post_category_id' => $tutorialCategory?->id,
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'is_featured' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($posts as $postData) {
            Post::updateOrCreate(
                ['slug' => $postData['slug']],
                $postData
            );
        }
    }

    private function getLaravelContent(): string
    {
        return <<<'HTML'
<h2>Wprowadzenie</h2>
<p>Laravel to framework PHP, który od lat dominuje w świecie web developmentu. W 2025 roku, wraz z wydaniem Laravel 12, framework ten oferuje jeszcze więcej możliwości dla deweloperów.</p>

<h2>Kluczowe zalety Laravel</h2>

<h3>1. Elegancka składnia</h3>
<p>Laravel słynie z czytelnego i ekspresyjnego kodu. Dzięki temu projekty są łatwiejsze w utrzymaniu, a nowi członkowie zespołu szybciej wdrażają się w kod.</p>

<blockquote>
<p>"Laravel sprawia, że programowanie w PHP znów jest przyjemnością."</p>
</blockquote>

<h3>2. Bogaty ekosystem</h3>
<p>Ecosystem Laravel to nie tylko framework - to cały zestaw narzędzi:</p>
<ul>
<li><strong>Laravel Forge</strong> - automatyzacja deploymentu</li>
<li><strong>Laravel Vapor</strong> - serverless deployment na AWS</li>
<li><strong>Laravel Nova / Filament</strong> - panele administracyjne</li>
<li><strong>Laravel Horizon</strong> - zarządzanie kolejkami</li>
</ul>

<h3>3. Świetna dokumentacja</h3>
<p>Dokumentacja Laravel jest wzorem dla innych projektów open-source. Każda funkcja jest szczegółowo opisana z praktycznymi przykładami.</p>

<h2>Kiedy wybrać Laravel?</h2>
<p>Laravel sprawdzi się idealnie gdy:</p>
<ol>
<li>Budujesz aplikację webową o średniej lub dużej skali</li>
<li>Potrzebujesz szybkiego prototypowania</li>
<li>Zależy Ci na bezpieczeństwie i dobrych praktykach</li>
<li>Planujesz długoterminowe utrzymanie projektu</li>
</ol>

<h2>Podsumowanie</h2>
<p>Laravel to dojrzały, stabilny framework z aktywną społecznością i ciągłym rozwojem. Jeśli szukasz technologii dla swojego następnego projektu, Laravel powinien być na szczycie Twojej listy.</p>
HTML;
    }

    private function getCrmContent(): string
    {
        return <<<'HTML'
<h2>Czym jest CRM?</h2>
<p>CRM (Customer Relationship Management) to system do zarządzania relacjami z klientami. Pozwala śledzić interakcje, zarządzać leadami i automatyzować procesy sprzedażowe.</p>

<h2>Korzyści z wdrożenia CRM</h2>

<h3>Lepsza organizacja danych</h3>
<p>Wszystkie informacje o klientach w jednym miejscu. Koniec z rozrzuconymi notatkami i arkuszami Excel.</p>

<h3>Automatyzacja procesów</h3>
<p>Automatyczne przypomnienia, follow-upy i raporty oszczędzają godziny pracy tygodniowo.</p>

<h3>Lepsze decyzje biznesowe</h3>
<p>Dashboardy i raporty pokazują, co działa, a co wymaga poprawy.</p>

<h2>5 kroków do udanego wdrożenia</h2>

<ol>
<li><strong>Zdefiniuj cele</strong> - Co chcesz osiągnąć? Więcej sprzedaży? Lepszą obsługę klienta?</li>
<li><strong>Wybierz odpowiednie narzędzie</strong> - Nie potrzebujesz Salesforce. Czasem prosty system wystarczy.</li>
<li><strong>Zaangażuj zespół</strong> - CRM działa tylko jeśli ludzie go używają.</li>
<li><strong>Zacznij od podstaw</strong> - Nie próbuj wdrażać wszystkiego naraz.</li>
<li><strong>Mierz i optymalizuj</strong> - Regularnie analizuj, czy system spełnia oczekiwania.</li>
</ol>

<h2>Ile kosztuje CRM?</h2>
<p>Koszty wahają się od darmowych rozwiązań (HubSpot Free, Bitrix24) przez kilkadziesiąt złotych miesięcznie za użytkownika (Pipedrive, Freshsales) po enterprise'owe rozwiązania za tysiące złotych.</p>

<blockquote>
<p>Dla małej firmy często najlepszym wyborem jest customowy CRM dostosowany do specyfiki branży.</p>
</blockquote>

<h2>Podsumowanie</h2>
<p>CRM to inwestycja, która zwraca się wielokrotnie. Kluczem jest odpowiednie planowanie i stopniowe wdrażanie funkcji.</p>
HTML;
    }

    private function getFilamentContent(): string
    {
        return <<<'HTML'
<h2>Co to jest Filament?</h2>
<p>Filament to nowoczesny framework do budowania paneli administracyjnych w Laravel. Wersja 5 przynosi wiele usprawnień i nowych funkcji.</p>

<h2>Wymagania</h2>
<ul>
<li>PHP 8.2+</li>
<li>Laravel 11 lub 12</li>
<li>Node.js i npm</li>
</ul>

<h2>Instalacja</h2>
<p>Zacznij od instalacji pakietu:</p>
<pre><code>composer require filament/filament:"^5.0"
php artisan filament:install --panels</code></pre>

<h2>Tworzenie pierwszego Resource</h2>
<p>Resource to podstawowy element Filament - reprezentuje model w panelu admina:</p>
<pre><code>php artisan make:filament-resource Post --generate</code></pre>

<p>Flaga <code>--generate</code> automatycznie tworzy formularz na podstawie struktury tabeli w bazie danych.</p>

<h2>Konfiguracja formularza</h2>
<p>Filament v5 wprowadza nową składnię dla formularzy:</p>
<pre><code>public static function form(Schema $schema): Schema
{
    return $schema-&gt;components([
        TextInput::make('title')
            -&gt;required()
            -&gt;maxLength(255),
        RichEditor::make('content')
            -&gt;columnSpanFull(),
    ]);
}</code></pre>

<h2>Tabela z danymi</h2>
<p>Konfiguracja tabeli jest równie prosta:</p>
<pre><code>public static function table(Table $table): Table
{
    return $table
        -&gt;columns([
            TextColumn::make('title')-&gt;searchable(),
            TextColumn::make('created_at')-&gt;dateTime(),
        ])
        -&gt;recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
}</code></pre>

<h2>Podsumowanie</h2>
<p>W zaledwie 30 minut możesz mieć w pełni funkcjonalny panel administracyjny. Filament to potężne narzędzie, które znacząco przyspiesza development.</p>
HTML;
    }
}
