<?php

namespace Digital\Logcleaner\Model;

class Days
{
    public const THIRTY_DAYS = 30;
    public const SIXTY_DAYS = 60;
    public const NINTY_DAYS = 90;
    public const SIX_MONTHS = 180;
    public const REMOVE_ALL = 'All';

    /**
     * Get available statuses.
     *
     * @return []
     */
    public function toOptionArray()
    {
        return [
            self::THIRTY_DAYS => __('1 Month'),
            self::SIXTY_DAYS => __('2 Months'),
            self::NINTY_DAYS => __('3 Months'),
            self::SIX_MONTHS => __('6 Months'),
            self::REMOVE_ALL => __('Remove All'),
        ];
    }
}
