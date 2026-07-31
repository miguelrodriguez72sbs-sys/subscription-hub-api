<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'total_revenue' => (float) Invoice::where('status', 'paid')
                ->whereBetween('paid_at', [$from, $to])->sum('amount'),
            'total_invoices' => Invoice::whereBetween('created_at', [$from, $to])->count(),
            'paid_invoices' => Invoice::where('status', 'paid')
                ->whereBetween('created_at', [$from, $to])->count(),
            'failed_invoices' => Invoice::where('status', 'failed')
                ->whereBetween('created_at', [$from, $to])->count(),
            'pending_invoices' => Invoice::where('status', 'pending')
                ->whereBetween('created_at', [$from, $to])->count(),
            'new_subscriptions' => Subscription::whereBetween('created_at', [$from, $to])->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
        ];
    }

    public function revenue(Request $request)
    {
        [$from, $to] = $this->range($request);

        $rows = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->get()
            ->groupBy(fn ($invoice) => $invoice->paid_at->format('Y-m'))
            ->map(fn ($group) => [
                'month' => $group->first()->paid_at->format('Y-m'),
                'total' => (float) $group->sum('amount'),
                'count' => $group->count(),
            ])
            ->values();

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'total' => (float) $rows->sum('total'),
            'by_month' => $rows,
        ];
    }

    public function subscriptions(Request $request)
    {
        [$from, $to] = $this->range($request);

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'by_status' => Subscription::whereBetween('created_at', [$from, $to])
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->get(),
            'by_day' => Subscription::whereBetween('created_at', [$from, $to])
                ->get()
                ->groupBy(fn ($sub) => $sub->created_at->format('Y-m-d'))
                ->map(fn ($group) => [
                    'day' => $group->first()->created_at->format('Y-m-d'),
                    'total' => $group->count(),
                ])
                ->values(),
        ];
    }

    public function invoices(Request $request)
    {
        [$from, $to] = $this->range($request);

        $invoices = Invoice::with('subscription.membershipPlan')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'total' => (float) $invoices->where('status', 'paid')->sum('amount'),
            'by_status' => $invoices->groupBy('status')
                ->map(fn ($group) => [
                    'status' => $group->first()->status,
                    'count' => $group->count(),
                    'amount' => (float) $group->sum('amount'),
                ])
                ->values(),
            'invoices' => $invoices,
        ];
    }

    protected function range(Request $request): array
    {
        $from = $request->date('from', now()->subDays(30)->startOfDay());
        $to = $request->date('to', now()->endOfDay());

        return [$from, $to];
    }
}
