<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Invoice;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->isAdmin()
            ? $this->adminStats()
            : $this->clientStats($request->user());
    }

    protected function adminStats(): array
    {
        return [
            'total_customers' => User::where('role', 'client')->count(),
            'total_plans' => MembershipPlan::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'pending_subscriptions' => Subscription::where('status', 'pending')->count(),
            'cancelled_subscriptions' => Subscription::where('status', 'cancelled')->count(),
            'total_revenue' => (float) Invoice::where('status', 'paid')->sum('amount'),
            'revenue_last_30_days' => (float) Invoice::where('status', 'paid')
                ->where('paid_at', '>=', now()->subDays(30))
                ->sum('amount'),
            'upcoming_renewals' => Subscription::where('status', 'active')
                ->where('next_billing_date', '<=', now()->addDays(7))
                ->count(),
            'recent_subscriptions' => SubscriptionResource::collection(
                Subscription::with('user', 'membershipPlan')->latest()->take(5)->get()
            ),
            'recent_invoices' => InvoiceResource::collection(
                Invoice::with('subscription')->latest()->take(5)->get()
            ),
        ];
    }

    protected function clientStats(User $user): array
    {
        return [
            'active_subscription' => $user->subscriptions()
                ->where('status', 'active')
                ->latest()
                ->first(),
            'total_subscriptions' => $user->subscriptions()->count(),
            'next_billing_date' => $user->subscriptions()
                ->where('status', 'active')
                ->orderBy('next_billing_date')
                ->value('next_billing_date'),
            'paid_amount' => (float) Invoice::whereHas('subscription', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'paid')->sum('amount'),
            'pending_invoices' => Invoice::whereHas('subscription', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'pending')->count(),
        ];
    }
}
