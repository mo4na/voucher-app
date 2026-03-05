<?php
declare(strict_types=1);

function get_previous_office(PDO $pdo, int $voucherId, string $currentOffice): ?string {
  $stmt = $pdo->prepare("
    SELECT to_office
    FROM voucher_history
    WHERE voucher_id = ?
      AND to_office IS NOT NULL
      AND to_office <> ?
    ORDER BY id DESC
    LIMIT 1
  ");
  $stmt->execute([$voucherId, $currentOffice]);
  $row = $stmt->fetch();
  return $row['to_office'] ?? null;
}