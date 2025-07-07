<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'currency',
        'rates'
    ];

    public static function currency(string $code): ?float
    {
        return static::where('currency', strtoupper($code))->value('rates');
    }
    public static function convertIDR(string $fromCurrency, float $amount): ?float
    {
        $idrRate = static::currency('IDR'); // e.g. 16000
        $fromRate = static::currency($fromCurrency); // e.g. EUR = 0.92

        if (!$idrRate || !$fromRate) return null;

        // 1. Convert dari currency asal ke USD
        $usdAmount = $amount / $fromRate;

        // 2. Convert dari USD ke IDR
        return $usdAmount * $idrRate;
    }
}
