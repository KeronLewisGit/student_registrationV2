<?php
// auth_bootstrap.php  (fixed)
declare(strict_types=1);

ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// Safe session path for cPanel
$sessionDir = __DIR__ . '/storage/sessions';
if (!is_dir($sessionDir)) { @mkdir($sessionDir, 0775, true); }
$cur = ini_get('session.save_path');
$curPath = $cur ? preg_replace('/^.*;/', '', $cur) : '';
if (!$curPath || !is_dir($curPath)) { session_save_path($sessionDir); }
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_name('slss_auth');
  @session_start();
}

require __DIR__ . '/vendor/autoload.php';
$pdo  = require __DIR__ . '/connect.php';
$auth = new \Delight\Auth\Auth($pdo);

use Delight\Auth\Role;

// === helpers ===
function redirectToLogin(): void {
  $next = $_SERVER['REQUEST_URI'] ?? '/';
  header('Location: /login.php?next=' . rawurlencode($next));
  exit;
}

function requireLogin(\Delight\Auth\Auth $auth): void {
  if (!$auth->isLoggedIn()) {
    redirectToLogin();
  }
}

function requireAnyRole(\Delight\Auth\Auth $auth, array $roles): void {
  if (!$auth->isLoggedIn()) {
    redirectToLogin();
  }
  foreach ($roles as $r) {
    if ($auth->hasRole($r)) return;
  }
  http_response_code(403);
  echo 'Forbidden';
  exit;
}

function currentUserId(\Delight\Auth\Auth $auth): ?int {
  return $auth->isLoggedIn() ? $auth->getUserId() : null;
}
