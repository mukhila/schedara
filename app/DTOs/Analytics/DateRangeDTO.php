<?php

namespace App\DTOs\Analytics;

use Carbon\Carbon;

class DateRangeDTO
{
    public readonly Carbon $from;
    public readonly Carbon $to;

    public function __construct(string $from, string $to)
    {
        $this->from = Carbon::parse($from)->startOfDay();
        $this->to   = Carbon::parse($to)->endOfDay();
    }

    public static function lastDays(int $days): self
    {
        return new self(
            now()->subDays($days - 1)->toDateString(),
            now()->toDateString()
        );
    }

    public static function lastMonth(): self  { return self::lastDays(30); }
    public static function lastQuarter(): self { return self::lastDays(90); }
    public static function lastYear(): self    { return self::lastDays(365); }

    public function fromString(): string { return $this->from->toDateString(); }
    public function toString(): string   { return $this->to->toDateString(); }
    public function diffInDays(): int    { return (int) $this->from->diffInDays($this->to) + 1; }
}
