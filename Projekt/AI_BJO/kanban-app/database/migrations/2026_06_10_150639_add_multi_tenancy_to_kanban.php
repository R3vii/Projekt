<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add project_id to tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('project_id')->after('id')->nullable()->constrained()->onDelete('cascade');
        });

        // Create pivot table for users and projects
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Migrate existing data to a default project
        $defaultProjectId = DB::table('projects')->insertGetId([
            'name' => 'Projekt Główny',
            'description' => 'Domyślny projekt utworzony podczas migracji.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tasks')->update(['project_id' => $defaultProjectId]);

        $users = DB::table('users')->where('role', '!=', 'admin')->get();
        $pivotData = [];
        foreach ($users as $user) {
            $pivotData[] = [
                'project_id' => $defaultProjectId,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($pivotData)) {
            DB::table('project_user')->insert($pivotData);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user');
        
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};
