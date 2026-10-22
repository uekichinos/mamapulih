<?php

namespace App\Enums;

/**
 * Shared vocabulary for dish tags and customer allergies/exclusions, so
 * exclusion-substitution on the prep sheet is an exact match rather than
 * free-text guesswork.
 */
enum DietTag: string
{
    case Seafood = 'seafood';
    case Beef = 'beef';
    case Spicy = 'spicy';
    case Nuts = 'nuts';
    case Dairy = 'dairy';
    case Egg = 'egg';
    case Gluten = 'gluten';
    case CsecSafe = 'csec_safe';

    public function label(): string
    {
        return match ($this) {
            self::Seafood => 'Seafood',
            self::Beef => 'Beef',
            self::Spicy => 'Spicy',
            self::Nuts => 'Nuts',
            self::Dairy => 'Dairy',
            self::Egg => 'Egg',
            self::Gluten => 'Gluten',
            self::CsecSafe => 'C-sec safe',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $tag) => $tag->value, self::cases());
    }
}
