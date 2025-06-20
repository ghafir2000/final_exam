<?php


namespace App\Enums;

class BookingEnums
{
    const PENDING = 0;
    const BOOKED = 1;
    const CANCELLED = 2;
    const STARTED = 3;
    const COMPLETED = 4;

    /**
     * Get all statuses as an array.
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::BOOKED,
            self::CANCELLED,
            self::COMPLETED,
            self::STARTED,
        ];
    }

    /**
     * Convert status integer to a readable name.
     */
    public static function label(int $status): string
    {
        $locale = app()->getLocale();
        return match ($locale) {
            'ar' => match ($status) {
                self::PENDING => 'قيد الانتظار',
                self::BOOKED => 'تم الحجز',
                self::CANCELLED => 'تم الالغاء',
                self::COMPLETED => 'تمت',
                self::STARTED => 'بدأت',

                default => 'غير معروف',
            },
            default => match ($status) {
                self::PENDING => 'Pending',
                self::BOOKED => 'Booked',
                self::CANCELLED => 'Canceled',
                self::COMPLETED => 'Completed',
                self::STARTED => 'Started',

                default => 'Unknown',
            },
        };
    }

    /**
     * Validate if a given status is valid.
     */
    public static function isValid(int $status): bool
    {
        return in_array($status, self::all(), true);
    }
}