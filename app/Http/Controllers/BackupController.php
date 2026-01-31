<?php

namespace App\Http\Controllers;

class BackupController extends Controller
{
    public function download(string $filename)
    {
        $user = auth()->user();

        if (! $user->hasRole('super_admin') && ! $user->hasRole('admin')) {
            abort(403, 'Acesso negado');
        }

        $service = new \App\Services\BackupService;
        $path = $service->getDownloadPath($filename);

        if (file_exists($path)) {
            return response()->download($path);
        }

        abort(404, 'Backup não encontrado');
    }
}
