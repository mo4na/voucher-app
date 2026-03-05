<?php
declare(strict_types=1);

/**
 * Automatic office hierarchy:
 * END_USER -> BUDGET -> ACCOUNTING -> CASHIER
 */
function next_office(string $currentOffice): ?string {
  return match ($currentOffice) {
    'END_USER' => 'BUDGET',
    'BUDGET' => 'ACCOUNTING',
    'ACCOUNTING' => 'CASHIER',
    'CASHIER' => null,
    default => null
  };
}

function for_receiving_status(string $office): string {
  return match ($office) {
    'BUDGET' => 'FOR_RECEIVING_BUDGET',
    'ACCOUNTING' => 'FOR_RECEIVING_ACCOUNTING',
    'CASHIER' => 'FOR_RECEIVING_CASHIER',
    'END_USER' => 'RETURNED_TO_ENDUSER',
    default => 'RETURNED_TO_ENDUSER'
  };
}

function received_status(string $office): string {
  return match ($office) {
    'BUDGET' => 'RECEIVED_BUDGET',
    'ACCOUNTING' => 'RECEIVED_ACCOUNTING',
    'CASHIER' => 'RECEIVED_CASHIER',
    default => 'RECEIVED_BUDGET'
  };
}