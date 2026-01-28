<?php

namespace App\Traits;

trait HasRolePermissions
{
    /**
     * Check if user has a specific role
     */
    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        $userRole = strtolower($this->role->name);
        
        if (is_array($roles)) {
            return in_array($userRole, array_map('strtolower', $roles));
        }

        return $userRole === strtolower($roles);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->hasRole(['manager', 'admin']);
    }

    /**
     * Check if user is supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->hasRole(['supervisor', 'manager', 'admin']);
    }

    /**
     * Check if user can approve refunds
     */
    public function canApproveRefunds(): bool
    {
        return $this->isSupervisor();
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers(): bool
    {
        return $this->hasRole(['admin', 'manager']);
    }

    /**
     * Check if user can view reports
     */
    public function canViewReports(): bool
    {
        return $this->hasRole(['admin', 'manager', 'supervisor']);
    }

    /**
     * Check if user can manage products
     */
    public function canManageProducts(): bool
    {
        return $this->hasRole(['admin', 'manager']);
    }

    /**
     * Check if user can process orders
     */
    public function canProcessOrders(): bool
    {
        return true; // All authenticated users can process orders
    }

    /**
     * Get role name
     */
    public function getRoleName(): string
    {
        return $this->role ? $this->role->name : 'No Role';
    }

    /**
     * Get role badge color
     */
    public function getRoleBadgeColor(): string
    {
        if (!$this->role) {
            return 'secondary';
        }

        return match(strtolower($this->role->name)) {
            'admin' => 'danger',
            'manager' => 'warning',
            'supervisor' => 'info',
            'cashier' => 'success',
            'waiter' => 'primary',
            'kitchen' => 'dark',
            default => 'secondary',
        };
    }
}
