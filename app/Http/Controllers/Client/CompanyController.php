<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function edit()
    {
        return view('client.settings.company', ['client' => Auth::user()->client]);
    }

    public function update(Request $request)
    {
        $client = Auth::user()->client;

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'owner_name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'gstin' => ['nullable', 'string', 'max:30'],
            'payment_details' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($client->logo_path) {
                Storage::disk('public')->delete($client->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $client->update($data);

        return back()->with('success', 'Company details updated.');
    }
}
