<?php
declare(strict_types=1);

function generate_voucher_number(PDO $pdo): string {
  $pdo->beginTransaction();

  $row = $pdo->query("SELECT last_number FROM voucher_sequences WHERE id=1 FOR UPDATE")->fetch();
  if (!$row) {
    $pdo->rollBack();
    throw new RuntimeException("voucher_sequences not initialized.");
  }

  $next = ((int)$row['last_number']) + 1;
  $pdo->prepare("UPDATE voucher_sequences SET last_number=? WHERE id=1")->execute([$next]);

  $pdo->commit();

  return "VCH-" . str_pad((string)$next, 6, "0", STR_PAD_LEFT);
}