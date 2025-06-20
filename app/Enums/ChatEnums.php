<?php


namespace App\Enums;

class ChatEnums
{
    const CLOSED = 0;
    const NEWMESSAGE = 1;
    const OPENED = 2;

    /**
     * Get all statuses as an array.
     */
    public static function all(): array
    {
        return [
            self::CLOSED,
            self::NEWMESSAGE,
            self::OPENED,
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
                self::CLOSED => 'مغلق',
                self::NEWMESSAGE => 'رسالة جديدة',
                self::OPENED => 'مفتوح',
                default => 'غير معروف',
            },
            default => match ($status) {
                self::CLOSED => 'Closed',
                self::NEWMESSAGE => 'New Message',
                self::OPENED => 'Opened',
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