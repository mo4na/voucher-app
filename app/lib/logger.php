<?php
declare(strict_types=1);

function log_action(PDO $pdo, ?int $userId, string $action, string $entity, ?int $entityId, ?string $details=null): void {
  $s = $pdo->prepare("INSERT INTO activity_logs(user_id, action, entity, entity_id, details) VALUES(?,?,?,?,?)");
  $s->execute([$userId, $action, $entity, $entityId, $details]);
}