<?php

namespace App\Config;

class Branding
{
    /**
     * Primary brand color (Tailwind class name without 'bg-' or 'text-' prefix)
     */
    public const PRIMARY_COLOR = 'blue-600';

    /**
     * Primary hover color (Tailwind class name without 'bg-' or 'text-' prefix)
     */
    public const PRIMARY_HOVER_COLOR = 'blue-700';

    /**
     * Primary light color for backgrounds (Tailwind class name without 'bg-' prefix)
     */
    public const PRIMARY_LIGHT_COLOR = 'blue-50';

    /**
     * Primary border color (Tailwind class name without 'border-' prefix)
     */
    public const PRIMARY_BORDER_COLOR = 'blue-200';

    /**
     * Primary text color on light backgrounds (Tailwind class name without 'text-' prefix)
     */
    public const PRIMARY_TEXT_COLOR = 'blue-600';

    /**
     * Primary text hover color (Tailwind class name without 'text-' prefix)
     */
    public const PRIMARY_TEXT_HOVER_COLOR = 'blue-500';

    /**
     * Secondary/accent color for gradients (Tailwind class name without 'bg-' prefix)
     */
    public const SECONDARY_COLOR = 'blue-800';

    /**
     * Secondary light color (Tailwind class name without 'bg-' or 'text-' prefix)
     */
    public const SECONDARY_LIGHT_COLOR = 'blue-100';

    /**
     * Focus ring color (Tailwind class name without 'ring-' prefix)
     */
    public const FOCUS_RING_COLOR = 'blue-500';

    /**
     * Application name
     */
    public const APP_NAME = 'LHDN Middleware';

    /**
     * Application tagline
     */
    public const APP_TAGLINE = 'MyInvois Invoice Submission Platform';

    /**
     * Get Tailwind classes for primary button
     */
    public static function primaryButton(): string
    {
        return 'bg-'.self::PRIMARY_COLOR.' hover:bg-'.self::PRIMARY_HOVER_COLOR.' focus:ring-'.self::FOCUS_RING_COLOR;
    }

    /**
     * Get Tailwind classes for primary text
     */
    public static function primaryText(): string
    {
        return 'text-'.self::PRIMARY_TEXT_COLOR.' hover:text-'.self::PRIMARY_TEXT_HOVER_COLOR;
    }

    /**
     * Get Tailwind classes for primary background
     */
    public static function primaryBackground(): string
    {
        return 'bg-'.self::PRIMARY_COLOR;
    }

    /**
     * Get Tailwind classes for gradient background
     */
    public static function gradientBackground(): string
    {
        return 'bg-gradient-to-br from-'.self::PRIMARY_COLOR.' to-'.self::SECONDARY_COLOR;
    }

    /**
     * Get Tailwind classes for light background
     */
    public static function lightBackground(): string
    {
        return 'bg-'.self::PRIMARY_LIGHT_COLOR.' border-'.self::PRIMARY_BORDER_COLOR;
    }

    /**
     * Get Tailwind classes for input focus
     */
    public static function inputFocus(): string
    {
        return 'focus:ring-'.self::FOCUS_RING_COLOR.' focus:border-'.self::FOCUS_RING_COLOR;
    }
}
