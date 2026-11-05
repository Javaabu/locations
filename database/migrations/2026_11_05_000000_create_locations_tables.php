<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Javaabu\Locations\Locations;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Locations::table('countries'), function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('alpha3_code', 3)->nullable()->unique();
            $table->string('numeric_code', 3)->nullable();
            $table->string('name');
            $table->string('dhivehi_name')->nullable();
            $table->string('official_name')->nullable();
            $table->string('calling_code', 8)->nullable()->index();
            $this->translations($table);
            $table->timestamps();
        });

        Schema::create(Locations::table('states'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained(Locations::table('countries'))->restrictOnDelete();
            $table->string('code', 10)->nullable()->unique();
            $table->string('name');
            $table->string('dhivehi_name')->nullable();
            $table->string('type')->nullable();
            $this->translations($table);
            $table->timestamps();
        });

        Schema::create(Locations::table('cities'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained(Locations::table('countries'))->restrictOnDelete();
            $table->foreignId('state_id')->nullable()->constrained(Locations::table('states'))->nullOnDelete();
            $table->string('code')->nullable()->index();
            $table->string('name');
            $table->string('dhivehi_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $this->translations($table);
            $table->timestamps();
        });

        Schema::create(Locations::table('atolls'), function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('dhivehi_code', 8)->nullable();
            $table->string('letter_code', 1)->nullable()->unique();
            $table->string('iso_code', 6)->nullable()->unique();
            $table->string('name');
            $table->string('dhivehi_name')->nullable();
            $table->string('official_name')->nullable();
            $table->string('official_dhivehi_name')->nullable();
            $table->boolean('is_city')->default(false);
            $table->unsignedSmallInteger('sort_order')->nullable();
            $this->translations($table);
            $table->timestamps();
        });

        Schema::create(Locations::table('islands'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('atoll_id')->constrained(Locations::table('atolls'))->restrictOnDelete();
            $table->string('code', 16)->unique();
            $table->string('name')->nullable()->index();
            $table->string('dhivehi_name')->nullable();
            $table->string('category', 32)->nullable()->index();
            $table->boolean('is_capital')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('area', 12, 4)->nullable();
            $this->translations($table);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Locations::table('islands'));
        Schema::dropIfExists(Locations::table('atolls'));
        Schema::dropIfExists(Locations::table('cities'));
        Schema::dropIfExists(Locations::table('states'));
        Schema::dropIfExists(Locations::table('countries'));
    }

    /**
     * Translations (javaabu/translatable's JSON storage). Harmless without it.
     */
    protected function translations(Blueprint $table): void
    {
        $table->json('translations')->nullable();
        $table->string('lang')->nullable()->index();
    }
};
