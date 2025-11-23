<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mapeamento de permissões antigas para novas (em português e resumidas)
        $permissionsMap = [
            'view_users' => 'Visualizar Usuários',
            'create_users' => 'Criar Usuários',
            'edit_users' => 'Editar Usuários',
            'delete_users' => 'Excluir Usuários',
            'view_credentials' => 'Visualizar Credenciais',
            'create_credentials' => 'Criar Credenciais',
            'edit_credentials' => 'Editar Credenciais',
            'delete_credentials' => 'Excluir Credenciais',
            'view_audit_logs' => 'Visualizar Logs',
            'export_reports' => 'Exportar Relatórios',
            'manage_permissions' => 'Gerenciar Permissões',
        ];

        foreach ($permissionsMap as $oldName => $newName) {
            DB::table('permissions')
                ->where('name', $oldName)
                ->update(['name' => $newName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para nomes em inglês
        $permissionsMap = [
            'Visualizar Usuários' => 'view_users',
            'Criar Usuários' => 'create_users',
            'Editar Usuários' => 'edit_users',
            'Excluir Usuários' => 'delete_users',
            'Visualizar Credenciais' => 'view_credentials',
            'Criar Credenciais' => 'create_credentials',
            'Editar Credenciais' => 'edit_credentials',
            'Excluir Credenciais' => 'delete_credentials',
            'Visualizar Logs' => 'view_audit_logs',
            'Exportar Relatórios' => 'export_reports',
            'Gerenciar Permissões' => 'manage_permissions',
        ];

        foreach ($permissionsMap as $newName => $oldName) {
            DB::table('permissions')
                ->where('name', $newName)
                ->update(['name' => $oldName]);
        }
    }
};
