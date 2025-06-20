<?php


namespace App\Enums;

class PaymentEnums
{
    const PENDING = 0;
    const SUCCESS = 1;
    const CANCELLED = 2;
    const REFUNDED = 3;

    /**
     * Get all statuses as an array.
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::SUCCESS,
            self::CANCELLED,
            self::REFUNDED,

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
                self::SUCCESS => 'تم الدفع',
                self::CANCELLED => 'ملغي',
                self::REFUNDED => 'مرتجع',
                default => 'غير معروف',
            },
            default => match ($status) {
                self::PENDING => 'Pending',
                self::SUCCESS => 'Completed',
                self::CANCELLED => 'Canceled',
                self::REFUNDED => 'Refunded',
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