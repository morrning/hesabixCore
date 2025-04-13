<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class NumberFormatExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('number_format', [$this, 'formatNumber']),
        ];
    }

    public function formatNumber($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',', $currency = null)
    {
        $formatted = number_format($number, $decimals, $decPoint, $thousandsSep);
        if ($currency) {
            $formatted .= ' ' . $currency;
        }
        return $formatted;
    }
} 