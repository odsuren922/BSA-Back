<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOAuthFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First check if the users table exists
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'oauth_id')) {
                    $table->string('oauth_id')->nullable();
                }
                
                if (!Schema::hasColumn('users', 'gid')) {
                    $table->string('gid')->nullable();
                }
                
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->nullable();
                }
            });
        }
        
        // Add fields to students table if it exists
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (!Schema::hasColumn('students', 'oauth_id')) {
                    $table->string('oauth_id')->nullable();
                }
                
                if (!Schema::hasColumn('students', 'gid')) {
                    $table->string('gid')->nullable();
                }
                
                if (!Schema::hasColumn('students', 'role')) {
                    $table->string('role')->nullable()->default('student');
                }
            });
        }
        
        // Add fields to teachers table if it exists
        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                if (!Schema::hasColumn('teachers', 'oauth_id')) {
                    $table->string('oauth_id')->nullable();
                }
                
                if (!Schema::hasColumn('teachers', 'gid')) {
                    $table->string('gid')->nullable();
                }
                
                if (!Schema::hasColumn('teachers', 'role')) {
                    $table->string('role')->nullable()->default('teacher');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove from users table if it exists
        if (Schema::hasTable('users') && 
            Schema::hasColumn('users', 'oauth_id') &&
            Schema::hasColumn('users', 'gid') &&
            Schema::hasColumn('users', 'role')) {
            
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['oauth_id', 'gid', 'role']);
            });
        }
        
        // Remove from students table if it exists
        if (Schema::hasTable('students') && 
            Schema::hasColumn('students', 'oauth_id') &&
            Schema::hasColumn('students', 'gid') &&
            Schema::hasColumn('students', 'role')) {
            
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn(['oauth_id', 'gid', 'role']);
            });
        }
        
        // Remove from teachers table if it exists
        if (Schema::hasTable('teachers') && 
            Schema::hasColumn('teachers', 'oauth_id') &&
            Schema::hasColumn('teachers', 'gid') &&
            Schema::hasColumn('teachers', 'role')) {
            
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropColumn(['oauth_id', 'gid', 'role']);
            });
        }
    }
}