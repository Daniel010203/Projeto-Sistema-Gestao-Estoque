<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

function startSession(): void { if (session_status() !== PHP_SESSION_ACTIVE) session_start(); }
function authenticatedUser(): ?array { startSession(); return $_SESSION['user'] ?? null; }
function requireAuthenticated(): array { $user = authenticatedUser(); if (!$user) { http_response_code(401); echo json_encode(['error' => 'Sessão expirada. Faça login novamente.']); exit; } return $user; }
function requireRole(array $roles): array { $user = requireAuthenticated(); if (!in_array($user['role'], $roles, true)) { http_response_code(403); echo json_encode(['error' => 'Você não possui permissão para esta ação.']); exit; } return $user; }
