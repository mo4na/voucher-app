<?php
declare(strict_types=1);

function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function money_php($amount): string {
  return 'PHP ' . number_format((float)$amount, 2);
}

function office_label(string $office): string {
  return match ($office) {
    'END_USER' => 'End User',
    'BUDGET' => 'Budget',
    'ACCOUNTING' => 'Accounting',
    'CASHIER' => 'Cashier',
    default => $office
  };
}