<?php
declare(strict_types=1);
require __DIR__ . '/auth_lib.php';
header('Content-Type: application/json; charset=utf-8');

function out(array $data, int $status = 200): never { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
function publicUser(array $user): array { return ['id'=>(int)$user['id'], 'name'=>$user['full_name'], 'email'=>$user['email'], 'role'=>$user['role'], 'segment'=>$user['business_segment']]; }
function body(): array { $input = json_decode(file_get_contents('php://input'), true); return is_array($input) ? $input : []; }

try {
  $db = database(); $input = body(); $action = $input['action'] ?? 'status';
  if ($action === 'status') { $user = authenticatedUser(); out(['user' => $user ? $user : null, 'hasUsers' => (bool)$db->query('SELECT COUNT(*) FROM users')->fetchColumn()]); }
  if ($action === 'logout') { startSession(); $_SESSION = []; session_destroy(); out(['message'=>'Sessão encerrada.']); }
  if ($action === 'unlock') {
    $current = requireAuthenticated(); $password = (string)($input['password'] ?? '');
    $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ? AND active = 1'); $stmt->execute([$current['id']]);
    if (!password_verify($password, (string)$stmt->fetchColumn())) out(['error'=>'Senha inválida.'], 401);
    out(['message'=>'Tela desbloqueada.']);
  }
  if ($action === 'login') {
    $email = trim((string)($input['email'] ?? '')); $password = (string)($input['password'] ?? '');
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND active = 1'); $stmt->execute([$email]); $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) out(['error'=>'E-mail ou senha inválidos.'], 401);
    startSession(); session_regenerate_id(true); $_SESSION['user'] = publicUser($user); out(['user'=>$_SESSION['user']]);
  }
  if ($action === 'request_reset') {
    $email = trim((string)($input['email'] ?? ''));
    $stmt = $db->prepare('SELECT id, full_name, email FROM users WHERE email = ? AND active = 1'); $stmt->execute([$email]); $user = $stmt->fetch();
    if ($user) {
      $token = bin2hex(random_bytes(32));
      $db->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL')->execute([$user['id']]);
      $db->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))')->execute([$user['id'], hash('sha256', $token)]);
      $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
      $link = $scheme . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '/codigo%20refatorado%2020%2007%202026.html?reset=' . $token;
      @mail($user['email'], 'Recuperação de acesso — E-Gestão WMS', "Olá, {$user['full_name']}. Use este link em até 30 minutos para redefinir sua senha:\n{$link}", "Content-Type: text/plain; charset=UTF-8\r\n");
    }
    out(['message'=>'Se o e-mail estiver cadastrado, enviaremos um link de recuperação.']);
  }
  if ($action === 'reset_password') {
    $token = (string)($input['token'] ?? ''); $password = (string)($input['password'] ?? '');
    if (strlen($token) !== 64 || strlen($password) < 8) out(['error'=>'Link ou senha inválidos.'], 422);
    $stmt = $db->prepare('SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()'); $stmt->execute([hash('sha256', $token)]); $reset = $stmt->fetch();
    if (!$reset) out(['error'=>'Este link expirou ou já foi utilizado.'], 422);
    $db->beginTransaction();
    $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $reset['user_id']]);
    $db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([$reset['id']]);
    $db->commit(); out(['message'=>'Senha atualizada. Faça login com a nova senha.']);
  }
  if ($action === 'register') {
    $count = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn(); $current = authenticatedUser();
    if ($count > 0 && (!$current || $current['role'] !== 'ADMIN')) out(['error'=>'Apenas administradores podem cadastrar usuários.'], 403);
    $name = trim((string)($input['name'] ?? '')); $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL); $password = (string)($input['password'] ?? '');
    if ($name === '' || !$email || strlen($password) < 8) out(['error'=>'Informe nome, e-mail válido e senha com ao menos 8 caracteres.'], 422);
    $role = $count === 0 ? 'ADMIN' : ($input['role'] ?? 'OPERADOR');
    if (!in_array($role, ['ADMIN','GESTOR','OPERADOR','AUDITOR'], true)) out(['error'=>'Perfil inválido.'], 422);
    $segment = $input['segment'] ?? 'CONSTRUCAO_CIVIL';
    $db->prepare('INSERT INTO users (full_name,email,password_hash,role,business_segment) VALUES (?,?,?,?,?)')->execute([$name,$email,password_hash($password, PASSWORD_DEFAULT),$role,$segment]);
    $id = (int)$db->lastInsertId();
    if ($count === 0) { startSession(); $_SESSION['user'] = ['id'=>$id,'name'=>$name,'email'=>$email,'role'=>'ADMIN','segment'=>$segment]; }
    out(['message'=>'Usuário cadastrado.', 'user'=>$count === 0 ? $_SESSION['user'] : null], 201);
  }
  out(['error'=>'Ação desconhecida.'], 404);
} catch (Throwable $e) { out(['error'=>'Não foi possível processar a autenticação.'], 500); }
