<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LeadFormController extends Controller
{
    public function index()
    {
        $client = Auth::user()->client;
        $url = route('public.lead-form', $client->client_code);

        return view('client.settings.lead-form', compact('client', 'url'));
    }
}
