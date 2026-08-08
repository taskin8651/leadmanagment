<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CustomFieldDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomFieldController extends Controller
{
    public function index()
    {
        $fields = Auth::user()->client->customFieldDefinitions()->get();
        return view('client.settings.custom-fields', compact('fields'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:text,number,date'],
        ]);

        $client = Auth::user()->client;
        $key = Str::slug($data['label'], '_');
        $data['key'] = $client->customFieldDefinitions()->where('key', $key)->exists() ? $key . '_' . Str::random(4) : $key;
        $data['client_id'] = $client->id;
        $data['sort_order'] = $client->customFieldDefinitions()->count();

        CustomFieldDefinition::create($data);
        return back()->with('success', 'Custom field added.');
    }

    public function destroy(CustomFieldDefinition $customField)
    {
        abort_unless($customField->client_id === Auth::user()->client->id, 403);
        $customField->delete();
        return back()->with('success', 'Custom field removed.');
    }
}
