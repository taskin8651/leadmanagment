<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $clientId = Auth::user()->client->id;

        $leads = Lead::where('client_id', $clientId)
            ->where(fn ($w) => $w->where('name', 'like', "%$q%")
                ->orWhere('phone', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")
                ->orWhere('lead_number', 'like', "%$q%"))
            ->limit(8)->get()
            ->map(fn ($lead) => [
                'title' => $lead->name,
                'subtitle' => $lead->lead_number,
                'url' => route('client.leads.show', $lead),
                'icon' => 'bi-person-lines-fill',
            ]);

        $groups = [];
        if ($leads->isNotEmpty()) $groups[] = ['label' => 'Leads', 'items' => $leads];

        return response()->json($groups);
    }
}
