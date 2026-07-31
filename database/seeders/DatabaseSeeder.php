<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $client = User::create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);

        $plans = [
            ['name' => 'Basico', 'description' => 'Plan mensual basico', 'price' => 9.99, 'duration_days' => 30],
            ['name' => 'Pro', 'description' => 'Plan mensual profesional', 'price' => 19.99, 'duration_days' => 30],
            ['name' => 'Premium', 'description' => 'Plan mensual premium', 'price' => 29.99, 'duration_days' => 30],
        ];

        foreach ($plans as $planData) {
            MembershipPlan::create($planData);
        }

        $proPlan = MembershipPlan::where('name', 'Pro')->first();

        $subscription = Subscription::create([
            'user_id' => $client->id,
            'membership_plan_id' => $proPlan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(20),
            'next_billing_date' => now()->addDays(20),
        ]);

        Invoice::create([
            'subscription_id' => $subscription->id,
            'amount' => $proPlan->price,
            'status' => 'paid',
            'payment_reference' => 'SIM-XXXXYYYYZZ',
            'paid_at' => now()->subDays(10),
        ]);
    }
}
