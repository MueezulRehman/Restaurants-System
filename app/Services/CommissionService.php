<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\CommissionEarning;
use App\Models\CommissionRule;

class CommissionService
{
    public static function generateForAppointment(Appointment $appointment): ?CommissionEarning
    {
        if ($appointment->status !== 'completed' || ! $appointment->staff_id || (float) $appointment->price <= 0) {
            return null;
        }

        $rule = CommissionRule::where('restaurant_id', $appointment->restaurant_id)
            ->where('staff_id', $appointment->staff_id)
            ->where('is_active', true)
            ->first();
        if (! $rule) {
            return null;
        }

        $baseAmount = (float) $appointment->price;
        $commission = $rule->type === 'percent'
            ? $baseAmount * ((float) $rule->value / 100)
            : (float) $rule->value;

        return CommissionEarning::firstOrCreate(
            ['restaurant_id' => $appointment->restaurant_id, 'appointment_id' => $appointment->id],
            ['staff_id' => $appointment->staff_id, 'base_amount' => $baseAmount, 'commission_amount' => round($commission, 2), 'status' => 'pending']
        );
    }
}
