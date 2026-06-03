<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'force_password_change')) {
                $table->boolean('force_password_change')->default(false)->after('last_login_ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'last_login_at')) {
                $columns[] = 'last_login_at';
            }
            if (Schema::hasColumn('users', 'last_login_ip')) {
                $columns[] = 'last_login_ip';
            }
            if (Schema::hasColumn('users', 'force_password_change')) {
                $columns[] = 'force_password_change';
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
