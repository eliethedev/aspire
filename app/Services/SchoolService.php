<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SchoolService
{
    public function createSchool(array $data): School
    {
        return DB::transaction(function () use ($data) {
            $school = School::create($data);

            // Create default settings if not provided
            if (!isset($data['settings'])) {
                $school->settings = $this->getDefaultSettings();
                $school->save();
            }

            return $school;
        });
    }

    public function addUserToSchool(School $school, User $user, string $role, bool $isActive = true): void
    {
        $school->schoolUsers()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'role' => $role,
                'is_active' => $isActive,
            ]
        );
    }

    public function removeUserFromSchool(School $school, User $user): void
    {
        $school->schoolUsers()->where('user_id', $user->id)->delete();
    }

    public function changeUserRole(School $school, User $user, string $newRole): void
    {
        $school->schoolUsers()
            ->where('user_id', $user->id)
            ->update(['role' => $newRole]);
    }

    public function deactivateUser(School $school, User $user): void
    {
        $school->schoolUsers()
            ->where('user_id', $user->id)
            ->update(['is_active' => false]);
    }

    public function activateUser(School $school, User $user): void
    {
        $school->schoolUsers()
            ->where('user_id', $user->id)
            ->update(['is_active' => true]);
    }

    public function getSchoolUsers(School $school, string $role = null)
    {
        $query = $school->schoolUsers()->with('user');

        if ($role) {
            $query->where('role', $role);
        }

        return $query->get();
    }

    public function getUserSchools(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->schoolUsers()
            ->with('school')
            ->where('is_active', true)
            ->get()
            ->pluck('school');
    }

    public function isUserActiveInSchool(School $school, User $user): bool
    {
        return $school->schoolUsers()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    public function getUserRoleInSchool(School $school, User $user): ?string
    {
        $schoolUser = $school->schoolUsers()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        return $schoolUser ? $schoolUser->role : null;
    }

    protected function getDefaultSettings(): array
    {
        return [
            'features' => [
                'observations' => true,
                'cot_ratings' => true,
                'predictions' => true,
                'feedback' => true,
            ],
            'limits' => [
                'max_users' => 100,
                'max_observations_per_month' => 1000,
            ],
            'notification_settings' => [
                'email_notifications' => true,
                'observation_reminders' => true,
                'feedback_notifications' => true,
            ],
        ];
    }

    public function updateSchoolSettings(School $school, array $settings): void
    {
        $currentSettings = $school->settings ?? [];
        $school->settings = array_merge($currentSettings, $settings);
        $school->save();
    }

    public function getSchoolSetting(School $school, string $key, $default = null)
    {
        return data_get($school->settings, $key, $default);
    }

    public function isFeatureEnabled(School $school, string $feature): bool
    {
        return $this->getSchoolSetting($school, "features.{$feature}", false);
    }
}
