<?php
declare(strict_types=1);

function escape(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_money(string|int|float|null $amount): string
{
    return 'Rs. ' . number_format((float) $amount, 2);
}
