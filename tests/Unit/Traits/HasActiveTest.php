<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Webfloo\Tests\TestCase;
use Webfloo\Traits\HasActive;

class HasActiveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('has_active_test', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
        });
    }

    public function test_active_scope_filters_to_is_active_true(): void
    {
        HasActiveTestModel::create(['name' => 'first', 'is_active' => true]);
        HasActiveTestModel::create(['name' => 'second', 'is_active' => false]);
        HasActiveTestModel::create(['name' => 'third', 'is_active' => true]);

        $this->assertSame(2, HasActiveTestModel::active()->count());
    }
}

/**
 * @property string $name
 * @property bool $is_active
 */
class HasActiveTestModel extends Model
{
    use HasActive;

    protected $table = 'has_active_test';

    public $timestamps = false;

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'bool'];
}
