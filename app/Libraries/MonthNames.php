<?php

<?php

/**
 * Month names correspondence table: Tigrigna / French
 * Used for financial register period labels
 */
class MonthNames
{
    /** @var array{1-12} Key: month number, Value: "Tigrigna / French" */
    protected static array $table = [
        1 => 'ጥሪ / Janvier',
        2 => 'ለካቲት / Février',
        3 => 'መጋቢት / Mars',
        4 => 'ሚያዝያ / Avril',
        5 => 'ግንቦት / Mai',
        6 => 'ሰነ / Juin',
        7 => 'ሓምለ / Juillet',
        8 => 'ነሓሰ / Août',
        9 => 'መስከረም / Septembre',
        10 => 'ጥቅምቲ / Octobre',
        11 => 'ሕዳር / Novembre',
        12 => 'ታሕሳስ / Décembre',
    ];

    /**
     * Get the month name correspondence.
     *
     * @param int $month 1-12
     * @return string "Tigrigna / French"
     */
    public static function get(int $month): string
    {
        return self::$table[$month] ?? 'Unknown month';
    }

    /**
     * Get all month correspondences.
     *
     * @return array{1-12}
     */
    public static function all(): array
    {
        return self::$table;
    }
}
```
```