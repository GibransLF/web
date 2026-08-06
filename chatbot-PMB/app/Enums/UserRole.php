<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SUPERVISOR = 'supervisor';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::SUPERVISOR => 'Supervisor',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::ADMIN => 'bg-indigo-50 text-[#1B287D] border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800',
            self::SUPERVISOR => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
        };
    }
}
