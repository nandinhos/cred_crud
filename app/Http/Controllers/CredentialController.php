<?php

namespace App\Http\Controllers;

use App\Models\Credential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CredentialController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     */
    public function index()
    {
        return view('credentials.index');
    }

    /**
     * Show the form for creating a new resource.
     * 
     */
    public function create()
    {
        return view('credentials.create');
    }

    
    /**
     * Display the specified resource.
     */
    public function store(Request $request)
    {
        // Validação dos campos enviados no formulário
        $request->validate([
            'fscs' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'secrecy' => 'required|string|in:R,S', // Verifica se o valor é "R" ou "S"
            'concession' => 'string|max:255',
            'validity' => 'required|string|max:255',
        ]);

        // Criação da nova credencial no banco de dados
        Credential::create([
            'fscs' => $request->fscs,
            'name' => $request->name,
            'secrecy' => $request->secrecy,
            'concession' => $request->concession,
            'validity' => $request->validity,
        ]);

        // Redirecionar para a página de listagem de credenciais ou qualquer outra página desejada
        return redirect()->route('credentials.index')->with('success', 'Credencial cadastrada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Credential $credential)
    {
        return view('credentials.edit', compact('credential'));
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Credential $credential)
    {
        // Validação dos campos enviados no formulário
        $request->validate([
            'fscs' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'secrecy' => 'required|string|max:255',
            'concession' => 'required|string|max:255',
            'validity' => 'required|string|max:255',
        ]);

        // Atualizar as informações da credencial com os dados do formulário
        $credential->update([
            'fscs' => $request->fscs,
            'name' => $request->name,
            'secrecy' => $request->secrecy,
            'concession' => $request->concession,
            'validity' => $request->validity,
        ]);

        // Redirecionar de volta à página de listagem de credenciais ou qualquer outra página desejada
        return redirect()->route('credentials.index')->with('success', 'Credencial atualizada com sucesso!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Credential $credential)
    {
        $credential->delete();

    return back()->with('success', 'Cliente excluído com sucesso.');
    }
}
