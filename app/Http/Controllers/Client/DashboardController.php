<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function scopeVisible($query)
    {
        $client = Auth::user()->client;
        $query->where('client_id', $client->id);
        if (!Auth::user()->hasRole('Admin')) {
            $query->where(fn ($q) => $q->where('assigned_to', Auth::id())->orWhereNull('assigned_to'));
        }
        return $query;
    }

    private function pctChange(float $current, float $previous): ?array
    {
        if ($previous == 0) {
            return $current > 0 ? ['value' => 100, 'up' => true] : null;
        }
        $pct = round((($current - $previous) / $previous) * 100, 1);
        return ['value' => abs($pct), 'up' => $pct >= 0];
    }

    public function index()
    {
        $client = Auth::user()->client;
        $thisMonthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        $leadsThisMonth = $this->scopeVisible(Lead::query())->where('created_at', '>=', $thisMonthStart)->count();
        $leadsLastMonth = $this->scopeVisible(Lead::query())->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        $wonThisMonth = $this->scopeVisible(Lead::query())->where('status', 'won')->where('updated_at', '>=', $thisMonthStart)->count();
        $wonLastMonth = $this->scopeVisible(Lead::query())->where('status', 'won')->whereBetween('updated_at', [$lastMonthStart, $lastMonthEnd])->count();

        $totalLeads = $this->scopeVisible(Lead::query())->count();
        $wonLeads = $this->scopeVisible(Lead::query())->where('status', 'won')->count();

        $followUpScope = fn () => FollowUp::whereHas('lead', fn ($q) => $this->scopeVisible($q));

        $todaysCalls = $followUpScope()->with('lead')
            ->whereDate('follow_up_at', today())
            ->where('status', 'pending')
            ->orderBy('follow_up_at')
            ->get();

        $staffPerformance = null;
        $sourceRoi = null;
        if (Auth::user()->hasRole('Admin')) {
            $staffPerformance = User::where('client_id', $client->id)
                ->where('is_active', true)
                ->get()
                ->map(function (User $user) use ($client) {
                    $assigned = Lead::where('client_id', $client->id)->where('assigned_to', $user->id);
                    $total = (clone $assigned)->count();
                    $won = (clone $assigned)->where('status', 'won')->count();
                    return [
                        'name' => $user->name,
                        'total' => $total,
                        'won' => $won,
                        'conversion' => $total ? round(($won / $total) * 100, 1) : 0,
                        'value' => (clone $assigned)->where('status', 'won')->sum('estimated_value'),
                    ];
                })
                ->filter(fn ($row) => $row['total'] > 0)
                ->sortByDesc('won')
                ->values();

            $sourceRoi = Lead::where('client_id', $client->id)
                ->selectRaw("source, count(*) as total, sum(case when status = 'won' then 1 else 0 end) as won, sum(case when status = 'won' then estimated_value else 0 end) as value")
                ->groupBy('source')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($row) => [
                    'source' => $row->source,
                    'total' => $row->total,
                    'won' => $row->won,
                    'conversion' => $row->total ? round(($row->won / $row->total) * 100, 1) : 0,
                    'value' => $row->value,
                ]);
        }

        return view('client.dashboard', [
            'staffPerformance' => $staffPerformance,
            'sourceRoi' => $sourceRoi,
            'client' => $client,
            'leads' => $totalLeads,
            'leadsDelta' => $this->pctChange($leadsThisMonth, $leadsLastMonth),
            'wonLeads' => $wonLeads,
            'wonDelta' => $this->pctChange($wonThisMonth, $wonLastMonth),
            'conversionRate' => $totalLeads ? round(($wonLeads / $totalLeads) * 100, 1) : 0,
            'todayFollowUps' => $todaysCalls->count(),
            'todaysCalls' => $todaysCalls,
            'overdueFollowUps' => $followUpScope()->where('follow_up_at', '<', now())->where('status', 'pending')->count(),
            'recentLeads' => $this->scopeVisible(Lead::query())->latest()->take(8)->get(),
            'chart' => app(AnalyticsController::class)->build(30),
        ]);
    }
}
