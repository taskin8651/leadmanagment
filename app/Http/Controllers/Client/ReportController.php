<?php

namespace App\Http\Controllers\Client;

use App\Exports\TelecallerReportExport;
use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private function range(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->subDays(6)->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();
        if ($from->gt($to)) [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        return [$from, $to];
    }

    private function build(Request $request): array
    {
        $clientId = Auth::user()->client_id;
        [$from, $to] = $this->range($request);

        $users = User::where('client_id', $clientId)->where('is_active', true)
            ->when($request->filled('telecaller_id'), fn ($q) => $q->where('id', $request->telecaller_id))
            ->orderBy('name')->get()->keyBy('id');

        $matrix = [];
        $touch = function ($day, $userId) use (&$matrix) {
            $matrix[$day][$userId] ??= [
                'leads_assigned' => 0, 'interactions' => 0, 'connected' => 0, 'rescheduled' => 0,
                'completed' => 0, 'pending' => 0, 'missed' => 0, 'won' => 0, 'lost' => 0,
            ];
            return $matrix[$day][$userId];
        };

        $leadsAssigned = LeadActivity::query()
            ->join('leads', 'leads.id', '=', 'lead_activities.lead_id')
            ->where('leads.client_id', $clientId)
            ->where('lead_activities.type', 'assigned')
            ->whereBetween('lead_activities.created_at', [$from, $to])
            ->selectRaw('DATE(lead_activities.created_at) as day, leads.assigned_to as user_id, COUNT(DISTINCT lead_activities.lead_id) as cnt')
            ->groupBy('day', 'leads.assigned_to')
            ->get();
        foreach ($leadsAssigned as $row) {
            if (!$row->user_id) continue;
            $touch($row->day, $row->user_id);
            $matrix[$row->day][$row->user_id]['leads_assigned'] = (int) $row->cnt;
        }

        $interactions = LeadActivity::query()
            ->join('leads', 'leads.id', '=', 'lead_activities.lead_id')
            ->where('leads.client_id', $clientId)
            ->whereIn('lead_activities.type', ['call', 'whatsapp', 'email'])
            ->whereBetween('lead_activities.created_at', [$from, $to])
            ->selectRaw('DATE(lead_activities.created_at) as day, lead_activities.user_id as user_id, COUNT(*) as cnt')
            ->groupBy('day', 'lead_activities.user_id')
            ->get();
        foreach ($interactions as $row) {
            if (!$row->user_id) continue;
            $touch($row->day, $row->user_id);
            $matrix[$row->day][$row->user_id]['interactions'] = (int) $row->cnt;
        }

        $connected = LeadActivity::query()
            ->join('leads', 'leads.id', '=', 'lead_activities.lead_id')
            ->where('leads.client_id', $clientId)
            ->where('lead_activities.type', 'call')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(lead_activities.meta, '$.outcome')) = 'Connected'")
            ->whereBetween('lead_activities.created_at', [$from, $to])
            ->selectRaw('DATE(lead_activities.created_at) as day, lead_activities.user_id as user_id, COUNT(*) as cnt')
            ->groupBy('day', 'lead_activities.user_id')
            ->get();
        foreach ($connected as $row) {
            if (!$row->user_id) continue;
            $touch($row->day, $row->user_id);
            $matrix[$row->day][$row->user_id]['connected'] = (int) $row->cnt;
        }

        $rescheduled = LeadActivity::query()
            ->join('leads', 'leads.id', '=', 'lead_activities.lead_id')
            ->where('leads.client_id', $clientId)
            ->where('lead_activities.type', 'follow_up_rescheduled')
            ->whereBetween('lead_activities.created_at', [$from, $to])
            ->selectRaw('DATE(lead_activities.created_at) as day, leads.assigned_to as user_id, COUNT(*) as cnt')
            ->groupBy('day', 'leads.assigned_to')
            ->get();
        foreach ($rescheduled as $row) {
            if (!$row->user_id) continue;
            $touch($row->day, $row->user_id);
            $matrix[$row->day][$row->user_id]['rescheduled'] = (int) $row->cnt;
        }

        $completed = FollowUp::query()
            ->join('leads', 'leads.id', '=', 'follow_ups.lead_id')
            ->where('leads.client_id', $clientId)
            ->where('follow_ups.status', 'completed')
            ->whereBetween('follow_ups.completed_at', [$from, $to])
            ->selectRaw('DATE(follow_ups.completed_at) as day, follow_ups.assigned_to as user_id, COUNT(*) as cnt')
            ->groupBy('day', 'follow_ups.assigned_to')
            ->get();
        foreach ($completed as $row) {
            if (!$row->user_id) continue;
            $touch($row->day, $row->user_id);
            $matrix[$row->day][$row->user_id]['completed'] = (int) $row->cnt;
        }

        foreach (['pending', 'missed'] as $status) {
            $rows = FollowUp::query()
                ->join('leads', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('leads.client_id', $clientId)
                ->where('follow_ups.status', $status)
                ->whereBetween('follow_ups.follow_up_at', [$from, $to])
                ->selectRaw('DATE(follow_ups.follow_up_at) as day, follow_ups.assigned_to as user_id, COUNT(*) as cnt')
                ->groupBy('day', 'follow_ups.assigned_to')
                ->get();
            foreach ($rows as $row) {
                if (!$row->user_id) continue;
                $touch($row->day, $row->user_id);
                $matrix[$row->day][$row->user_id][$status] = (int) $row->cnt;
            }
        }

        foreach (['won', 'lost'] as $status) {
            $rows = Lead::query()
                ->where('client_id', $clientId)
                ->where('status', $status)
                ->whereBetween('updated_at', [$from, $to])
                ->selectRaw('DATE(updated_at) as day, assigned_to as user_id, COUNT(*) as cnt')
                ->groupBy('day', 'assigned_to')
                ->get();
            foreach ($rows as $row) {
                if (!$row->user_id) continue;
                $touch($row->day, $row->user_id);
                $matrix[$row->day][$row->user_id][$status] = (int) $row->cnt;
            }
        }

        $rows = collect();
        $period = Carbon::parse($from)->toPeriod($to);
        foreach ($period as $date) {
            $day = $date->toDateString();
            foreach ($users as $userId => $user) {
                $stats = $matrix[$day][$userId] ?? null;
                if (!$stats) continue;
                $rows->push(array_merge(['date' => $day, 'telecaller' => $user->name, 'user_id' => $userId], $stats));
            }
        }
        $rows = $rows->sortBy([['date', 'desc'], ['telecaller', 'asc']])->values();

        return compact('rows', 'users', 'from', 'to');
    }

    public function index(Request $request)
    {
        $data = $this->build($request);
        $totals = [
            'leads_assigned' => $data['rows']->sum('leads_assigned'),
            'interactions' => $data['rows']->sum('interactions'),
            'connected' => $data['rows']->sum('connected'),
            'rescheduled' => $data['rows']->sum('rescheduled'),
            'completed' => $data['rows']->sum('completed'),
            'pending' => $data['rows']->sum('pending'),
            'missed' => $data['rows']->sum('missed'),
            'won' => $data['rows']->sum('won'),
            'lost' => $data['rows']->sum('lost'),
        ];
        return view('client.reports.index', [
            'rows' => $data['rows'],
            'users' => $data['users'],
            'from' => $data['from'],
            'to' => $data['to'],
            'totals' => $totals,
        ]);
    }

    public function export(Request $request)
    {
        $data = $this->build($request);
        $filename = 'telecaller-report-' . $data['from']->format('Y-m-d') . '-to-' . $data['to']->format('Y-m-d') . '.csv';
        return Excel::download(new TelecallerReportExport($data['rows']), $filename, \Maatwebsite\Excel\Excel::CSV);
    }
}
