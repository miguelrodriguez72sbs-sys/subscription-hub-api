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

        $apps = [
            'Netflix' => [
                ['name' => 'Basica', 'description' => 'Calidad estandar (720p), 1 pantalla', 'price' => 6.99, 'duration_days' => 30],
                ['name' => 'Estandar', 'description' => 'Calidad Full HD, 2 pantallas', 'price' => 10.99, 'duration_days' => 30],
                ['name' => 'Premium', 'description' => 'Calidad 4K UHD, 4 pantallas', 'price' => 15.99, 'duration_days' => 30],
            ],
            'Spotify' => [
                ['name' => 'Individual', 'description' => 'Musica en streaming para 1 cuenta', 'price' => 5.99, 'duration_days' => 30],
                ['name' => 'Duo', 'description' => '2 cuentas premium', 'price' => 8.99, 'duration_days' => 30],
                ['name' => 'Familiar', 'description' => 'Hasta 6 cuentas', 'price' => 11.99, 'duration_days' => 30],
            ],
            'YouTube' => [
                ['name' => 'Individual', 'description' => 'YouTube sin anuncios + YouTube Music', 'price' => 6.99, 'duration_days' => 30],
                ['name' => 'Familiar', 'description' => 'Hasta 5 miembros', 'price' => 11.99, 'duration_days' => 30],
            ],
            'Amazon' => [
                ['name' => 'Prime', 'description' => 'Envio rapido + Prime Video + Music', 'price' => 4.99, 'duration_days' => 30],
                ['name' => 'Prime Anual', 'description' => 'Pago anual con descuento', 'price' => 49.99, 'duration_days' => 365],
            ],
            'Disney' => [
                ['name' => 'Normal', 'description' => 'Calidad Full HD, 2 pantallas', 'price' => 7.99, 'duration_days' => 30],
                ['name' => 'Premium', 'description' => 'Calidad 4K, 4 pantallas', 'price' => 11.99, 'duration_days' => 30],
            ],
        ];

        foreach ($apps as $application => $plans) {
            foreach ($plans as $planData) {
                MembershipPlan::create($planData + ['application' => $application]);
            }
        }

        $proPlan = MembershipPlan::where('application', 'Netflix')->where('name', 'Estandar')->first();

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
