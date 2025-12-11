<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\LoginHistory; 

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // 1. Buscamos sesiones activas viejas (más de 24 horas)
    $expiredSessions = LoginHistory::where('status', 'active')
        ->where('login_at', '<', now()->subHours(24))
        ->get();

    // 2. Las cerramos
    foreach ($expiredSessions as $session) {
        $session->update([
            'logout_at' => $session->login_at->addHours(24),
            'status' => 'expired'
        ]);
    }
})->hourly(); // Se ejecuta cada hora
