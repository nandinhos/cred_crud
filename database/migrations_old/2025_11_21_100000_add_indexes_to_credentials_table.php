<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Índice único para fscs (se ainda não existir)
        if (! $this->indexExists('credentials', 'credentials_fscs_unique')) {
            Schema::table('credentials', function (Blueprint $table): void {
                if (Schema::hasColumn('credentials', 'fscs')) {
                    $table->unique('fscs', 'credentials_fscs_unique');
                }
            });
        }

        // Índice para validity
        if (! $this->indexExists('credentials', 'credentials_validity_index')) {
            Schema::table('credentials', function (Blueprint $table): void {
                if (Schema::hasColumn('credentials', 'validity')) {
                    $table->index('validity', 'credentials_validity_index');
                }
            });
        }

        // Índice para created_at
        if (! $this->indexExists('credentials', 'credentials_created_at_index')) {
            Schema::table('credentials', function (Blueprint $table): void {
                if (Schema::hasColumn('credentials', 'created_at')) {
                    $table->index('created_at', 'credentials_created_at_index');
                }
            });
        }

        // Índice composto para user_id + validity
        if (! $this->indexExists('credentials', 'credentials_user_validity_index')) {
            Schema::table('credentials', function (Blueprint $table): void {
                if (Schema::hasColumn('credentials', 'user_id') && Schema::hasColumn('credentials', 'validity')) {
                    $table->index(['user_id', 'validity'], 'credentials_user_validity_index');
                }
            });
        }

        // Índice para secrecy
        if (! $this->indexExists('credentials', 'credentials_secrecy_index')) {
            Schema::table('credentials', function (Blueprint $table): void {
                if (Schema::hasColumn('credentials', 'secrecy')) {
                    $table->index('secrecy', 'credentials_secrecy_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter apenas os índices criados aqui (não remover o unique de fscs conforme orientação da task)
        if ($this->indexExists('credentials', 'credentials_secrecy_index')) {
            Schema::table('credentials', function (Blueprint $table): void {
                $table->dropIndex('credentials_secrecy_index');
            });
        }

        if ($this->indexExists('credentials', 'credentials_user_validity_index')) {
            Schema::table('credentials', function (Blueprint $table): void {
                $table->dropIndex('credentials_user_validity_index');
            });
        }

        if ($this->indexExists('credentials', 'credentials_created_at_index')) {
            Schema::table('credentials', function (Blueprint $table): void {
                $table->dropIndex('credentials_created_at_index');
            });
        }

        if ($this->indexExists('credentials', 'credentials_validity_index')) {
            Schema::table('credentials', function (Blueprint $table): void {
                $table->dropIndex('credentials_validity_index');
            });
        }
    }

    /**
     * Check if an index exists on a given table.
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        // Procura o índice pelo nome com SHOW INDEX
        $result = DB::select('SHOW INDEX FROM `'.$table.'` WHERE `Key_name` = ?', [$indexName]);

        return ! empty($result);
    }
};
