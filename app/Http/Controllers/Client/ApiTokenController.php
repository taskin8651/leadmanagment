<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTokenController extends Controller
{
    public function index()
    {
        $tokens = Auth::user()->tokens()->latest()->get();
        return view('client.settings.api-tokens', compact('tokens'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $token = Auth::user()->createToken($data['name']);
        AuditLog::record('api_token.created', "API token '{$data['name']}' created");
        return back()->with('plainTextToken', $token->plainTextToken)->with('success', 'API token created — copy it now, it won\'t be shown again.');
    }

    public function destroy(string $id)
    {
        $token = Auth::user()->tokens()->where('id', $id)->first();
        Auth::user()->tokens()->where('id', $id)->delete();
        AuditLog::record('api_token.revoked', "API token '" . ($token->name ?? $id) . "' revoked");
        return back()->with('success', 'Token revoked.');
    }
}
