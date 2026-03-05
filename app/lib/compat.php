<?php
declare(strict_types=1);

function starts_with(string $haystack, string $needle): bool {
  return $needle === '' || substr($haystack, 0, strlen($needle)) === $needle;
}