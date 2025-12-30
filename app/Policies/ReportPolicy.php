<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can generate a given report type.
     */
    public function generate(User $user, string $type): bool
    {
        $role = $this->resolveRole($user);
        $access = config('reports.access');

        $allowed = $access[$role] ?? [];
        return in_array('*', $allowed, true) || in_array($type, $allowed, true);
    }

    /**
     * Determine whether the user can view/download a stored report.
     */
    public function view(User $user): bool
    {
        // All roles that can generate can also view.
        return true;
    }

    public function resolveRole(User $user): string
    {
        // Try common role resolvers without hard dependency on a specific package.
        if (method_exists($user, 'getRoleName')) {
            return $this->normalizeRole((string) $user->getRoleName());
        }
        if (method_exists($user, 'hasRole')) {
            foreach (array_keys(config('reports.access')) as $candidate) {
                if ($user->hasRole($candidate)) {
                    return $this->normalizeRole($candidate);
                }
            }
        }
        // Use Eloquent attribute access rather than property_exists
        $roleValue = $user->getAttribute('role');
        if (!is_string($roleValue)) {
            $roleValue = $user->role ?? null; // magic accessor fallback
        }
        if (is_string($roleValue) && $roleValue !== '') {
            return $this->normalizeRole($roleValue);
        }
        // Fallback to a conservative role
        return 'adviser';
    }

    private function normalizeRole(string $role): string
    {
        $key = strtolower(trim($role));
        $aliases = [
            'administrator' => 'admin',
            'superadmin' => 'admin',
            'advisor' => 'adviser',
            'requester' => 'requestor',
            'student' => 'requestor',
        ];
        return $aliases[$key] ?? $key;
    }
}
