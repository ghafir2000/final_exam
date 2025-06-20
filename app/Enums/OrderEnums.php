<?php


namespace App\Enums;

class OrderEnums
{
    const PENDING = 0;
    const ORDERED = 1;
    const CANCELLED = 2;
    const SHIPPED = 3;

    /**
     * Get all statuses as an array.
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::ORDERED,
            self::CANCELLED,
            self::SHIPPED
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
                self::ORDERED => 'تم الطلب',
                self::CANCELLED => 'تم الالغاء',
                self::SHIPPED => 'تم الشحن',
                default => 'غير معروف',
            },
            default => match ($status) {
                self::PENDING => 'Pending',
                self::ORDERED => 'Ordered',
                self::CANCELLED => 'Canceled',
                self::SHIPPED => 'Shipped',
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