<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
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

    public function index(Request $request)
    {
        return response()->json($this->build((int) $request->get('range', 30)));
    }

    public function build(int $range): array
    {
        $days = in_array($range, [7, 30, 90, 365]) ? $range : 30;
        $start = Carbon::today()->subDays($days - 1);
        $byMonth = $days > 90;

        $leadGrowth = $this->series(
            $this->scopeVisible(Lead::query())->where('created_at', '>=', $start)->get()
                ->groupBy(fn ($l) => $byMonth ? $l->created_at->format('Y-m') : $l->created_at->format('Y-m-d')),
            $start, $days, $byMonth
        );

        $leadSources = $this->scopeVisible(Lead::query())->selectRaw('source, count(*) as c')->groupBy('source')->orderByDesc('c')->limit(6)->pluck('c', 'source');
        $leadStatus = $this->scopeVisible(Lead::query())->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $totalLeads = $this->scopeVisible(Lead::query())->count();
        $wonLeads = $this->scopeVisible(Lead::query())->where('status', 'won')->count();
        $conversionRate = $totalLeads ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

        return [
            'leadGrowth' => $leadGrowth,
            'leadSources' => ['labels' => $leadSources->keys(), 'data' => $leadSources->values()],
            'leadStatus' => ['labels' => $leadStatus->keys()->map(fn ($s) => ucfirst(str_replace('-', ' ', $s))), 'data' => $leadStatus->values()],
            'conversionRate' => $conversionRate,
        ];
    }

    private function series($grouped, Carbon $start, int $days, bool $byMonth)
    {
        $labels = [];
        $data = [];
        if ($byMonth) {
            $months = (int) ceil($days / 30);
            for ($i = $months - 1; $i >= 0; $i--) {
                $d = Carbon::today()->subMonths($i);
                $key = $d->format('Y-m');
                $labels[] = $d->format('M Y');
                $bucket = $grouped->get($key);
                $data[] = $bucket ? $bucket->count() : 0;
            }
        } else {
            for ($i = 0; $i < $days; $i++) {
                $d = $start->copy()->addDays($i);
                $key = $d->format('Y-m-d');
                $labels[] = $d->format('d M');
                $bucket = $grouped->get($key);
                $data[] = $bucket ? $bucket->count() : 0;
            }
        }
        return ['labels' => $labels, 'data' => $data];
    }
}
