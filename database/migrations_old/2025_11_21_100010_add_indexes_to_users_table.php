<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índice para email (apenas se não existir um índice com este nome específico)
        if (! $this->indexExists('users', 'users_email_index')) {
            Schema::table('users', function (Blueprint $table): void {
                if (Schema::hasColumn('users', 'email')) {
                    // Evita duplicar índice se unique já existe: criamos somente se o nome específico não existir
                    $table->index('email', 'users_email_index');
                }
            });
        }

        // Índice para created_at
        if (! $this->indexExists('users', 'users_created_at_index')) {
            Schema::table('users', function (Blueprint $table): void {
                if (Schema::hasColumn('users', 'created_at')) {
                    $table->index('created_at', 'users_created_at_index');
                }
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('users', 'users_created_at_index')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex('users_created_at_index');
            });
        }

        if ($this->indexExists('users', 'users_email_index')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex('users_email_index');
            });
        }
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select('SHOW INDEX FROM `'.$table.'` WHERE `Key_name` = ?', [$indexName]);

        return ! empty($result);
    }
};
