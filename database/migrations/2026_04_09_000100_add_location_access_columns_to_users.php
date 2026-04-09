<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'access_scope')) {
                $table->string('access_scope', 32)
                    ->default('single_location')
                    ->after('location_id')
                    ->index();
            }

            if (! Schema::hasColumn('users', 'active_location_id')) {
                $table->unsignedBigInteger('active_location_id')
                    ->nullable()
                    ->after('location_id')
                    ->index();
            }
        });

        if (Schema::hasColumn('users', 'active_location_id') && Schema::hasTable('locations')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->foreign('active_location_id')
                    ->references('id')
                    ->on('locations')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('user_locations')) {
            Schema::create('user_locations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('location_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'location_id']);
                $table->index('location_id');
            });
        }

        $users = DB::table('users')->select('id', 'location_id', 'active_location_id')->get();

        foreach ($users as $user) {
            if ($user->location_id !== null) {
                $exists = DB::table('user_locations')
                    ->where('user_id', (int) $user->id)
                    ->where('location_id', (int) $user->location_id)
                    ->exists();

                if (! $exists) {
                    DB::table('user_locations')->insert([
                        'user_id' => (int) $user->id,
                        'location_id' => (int) $user->location_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($user->active_location_id === null && $user->location_id !== null) {
                DB::table('users')
                    ->where('id', (int) $user->id)
                    ->update(['active_location_id' => (int) $user->location_id]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'active_location_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropForeign(['active_location_id']);
            });
        }

        Schema::dropIfExists('user_locations');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (Schema::hasColumn('users', 'active_location_id')) {
                    $table->dropColumn('active_location_id');
                }

                if (Schema::hasColumn('users', 'access_scope')) {
                    $table->dropColumn('access_scope');
                }
            });
        }
    }
};
