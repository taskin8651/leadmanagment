<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Imports\LeadsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LeadImportController extends Controller
{
    public function show()
    {
        abort_unless(Auth::user()->can('create_leads'), 403);
        return view('client.leads.import');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('create_leads'), 403);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx']]);

        $import = new LeadsImport(Auth::user()->client->id, Auth::id());
        Excel::import($import, $request->file('file'));

        return redirect()->route('client.leads.index')->with(
            'success',
            "Import complete: {$import->imported} added, {$import->skipped} duplicates skipped, {$import->errored} rows had missing name/phone."
        );
    }
}
