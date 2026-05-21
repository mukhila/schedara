<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending    = 'pending';
    case InProgress = 'in_progress';
    case Review     = 'review';
    case Completed  = 'completed';
    case Rejected   = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::InProgress => 'In Progress',
            self::Review     => 'In Review',
            self::Completed  => 'Completed',
            self::Rejected   => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending    => '#6b7280',
            self::InProgress => '#3b82f6',
            self::Review     => '#f59e0b',
            self::Completed  => '#10b981',
            self::Rejected   => '#ef4444',
        };
    }

    public function bgColor(): string
    {
        return match ($this) {
            self::Pending    => 'rgba(107,114,128,.1)',
            self::InProgress => 'rgba(59,130,246,.1)',
            self::Review     => 'rgba(245,158,11,.1)',
            self::Completed  => 'rgba(16,185,129,.1)',
            self::Rejected   => 'rgba(239,68,68,.1)',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Rejected]);
    }

    public static function kanbanOrder(): array
    {
        return [self::Pending, self::InProgress, self::Review, self::Completed, self::Rejected];
    }
}
