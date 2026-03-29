<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\Extracurricular;
use App\Models\User;

class AssessmentPolicy
{
    public function viewCoachDashboard(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'pembina']);
    }

    public function viewAdminReport(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Pembina hanya bisa menilai di eskul yang dia bina
     */
    public function manageAssessment(User $user, Extracurricular $extracurricular): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('pembina') &&
            $extracurricular->coaches()->where('user_id', $user->id)->exists();
    }
}