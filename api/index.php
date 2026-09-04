<?php
declare(strict_types=1);

require __DIR__ . '/database.php';
require __DIR__ . '/auth_lib.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function respond(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function input(): array
{
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) respond(['error' => 'Corpo da requisição inválido.'], 400);
    return $body;
}

function requireText(array $data, string $key): string
{
    $value = trim((string)($data[$key] ?? ''));
    if ($value === '') respond(['error' => "O campo {$key} é obrigatório."], 422);
    return $value;
}

function requirePositive(array $data, string $key): int
{
    $value = filter_var($data[$key] ?? null, FILTER_VALIDATE_INT);
    if ($value === false || $value < 1) respond(['error' => "O campo {$key} deve ser maior que zero."], 422);
    return $value;
}

function audit(PDO $db, string $action, string $operator = 'Sistema'): void
{
    $stmt = $db->prepare('INSERT INTO audit_logs (operator_name, action_description, record_hash) VALUES (?, ?, ?)');
    $stmt->execute([$operator, $action, strtoupper(bin2hex(random_bytes(8)))]);
}

function notifyManagers(PDO $db, int $movementId, string $subject, string $message): string
{
    $recipients = $db->query("SELECT email FROM users WHERE active = 1 AND role IN ('ADMIN','GESTOR')")->fetchAll(PDO::FETCH_COLUMN);
    if (!$recipients) return 'NAO';
    $allSent = true;
    foreach ($recipients as $email) {
        $sent = @mail($email, $subject, $message, "Content-Type: text/plain; charset=UTF-8\r\n");
        $db->prepare('INSERT INTO email_notifications (movement_id, recipient, subject, status) VALUES (?, ?, ?, ?)')->execute([$movementId, $email, $subject, $sent ? 'SIM' : 'NAO']);
        $allSent = $allSent && $sent;
    }
    return $allSent ? 'SIM' : 'NAO';
}

function items(PDO $db): array
{
    return $db->query('SELECT id, name AS nome, barcode, category AS categoria, risk AS risco, turnover AS rotatividade, packaging AS embalagem, quantity AS qtd, minimum_stock AS min, location_code AS local, segment AS segmento FROM items WHERE active = 1 ORDER BY name')->fetchAll();
}

function snapshot(PDO $db, array $user): array
{
    $orders = $db->query("SELECT p.order_code AS id, p.strategy AS estrategia, p.item_id AS itemId, i.name AS itemNome, i.barcode, i.location_code AS local, p.quantity AS qtd, p.customer_order AS pedido, p.status FROM picking_orders p JOIN items i ON i.id = p.item_id ORDER BY p.id DESC")->fetchAll();
    $logs = $db->query('SELECT DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") AS time, operator_name AS user, action_description AS acao, record_hash AS hash FROM audit_logs ORDER BY id DESC LIMIT 50')->fetchAll();
    $deletions = [];
    if (in_array($user['role'], ['ADMIN', 'GESTOR'], true)) {
        $deletions = $db->query("SELECT d.id, i.name AS itemName, u.full_name AS requestedBy, d.reason, d.created_at FROM deletion_requests d JOIN items i ON i.id=d.item_id JOIN users u ON u.id=d.requested_by WHERE d.status='PENDENTE' ORDER BY d.id DESC")->fetchAll();
    }
    return ['items' => items($db), 'orders' => $orders, 'audits' => $logs, 'deletionRequests' => $deletions, 'user' => $user];
}

try {
    $db = database();
    $user = requireAuthenticated();
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') respond(snapshot($db, $user));
    if ($method !== 'POST') respond(['error' => 'Método não permitido.'], 405);

    $data = input();
    $action = requireText($data, 'action');
    $operator = $user['name'];

    if ($action === 'create_item') {
        $db->beginTransaction();
        $stmt = $db->prepare('INSERT INTO items (name, barcode, category, risk, turnover, packaging, quantity, minimum_stock, location_code, segment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([requireText($data, 'nome'), requireText($data, 'barcode'), requireText($data, 'categoria'), requireText($data, 'risco'), requireText($data, 'rotatividade'), requireText($data, 'embalagem'), requirePositive($data, 'qtd'), requirePositive($data, 'min'), requireText($data, 'local'), trim((string)($data['segmento'] ?? 'Geral')) ?: 'Geral']);
        $itemId = (int)$db->lastInsertId();
        $db->prepare('INSERT INTO receipts (item_id, invoice_number, invoice_total, received_at, supplier_name, vehicle, driver_name, license_plate, received_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([$itemId, requireText($data, 'invoiceNumber'), $data['invoiceTotal'] ?? null, requireText($data, 'receivedAt'), requireText($data, 'supplier'), requireText($data, 'vehicle'), requireText($data, 'driver'), requireText($data, 'plate'), $user['id']]);
        audit($db, 'Cadastrou ativo: ' . $data['nome'], $operator);
        $db->commit();
        respond(['message' => 'Ativo cadastrado com sucesso.', 'itemId' => $itemId, 'data' => snapshot($db, $user)], 201);
    }

    $db->beginTransaction();
    if ($action === 'request_deletion') {
        $itemId = requirePositive($data, 'itemId');
        $reason = requireText($data, 'reason');
        $exists = $db->prepare('SELECT id, name FROM items WHERE id = ? AND active = 1 FOR UPDATE'); $exists->execute([$itemId]); $item = $exists->fetch();
        if (!$item) throw new RuntimeException('Item não encontrado ou já excluído.');
        $db->prepare('INSERT INTO deletion_requests (item_id, requested_by, reason) VALUES (?, ?, ?)')->execute([$itemId, $user['id'], $reason]);
        audit($db, "Solicitou exclusão do item {$item['name']}", $operator);
    } elseif ($action === 'approve_deletion') {
        if (!in_array($user['role'], ['ADMIN', 'GESTOR'], true)) throw new RuntimeException('Apenas gestor ou administrador pode aprovar exclusões.');
        $requestId = requirePositive($data, 'requestId'); $password = (string)($data['password'] ?? '');
        $auth = $db->prepare('SELECT password_hash FROM users WHERE id = ?'); $auth->execute([$user['id']]); $hash = $auth->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) throw new RuntimeException('Senha de confirmação inválida.');
        $stmt = $db->prepare("SELECT * FROM deletion_requests WHERE id = ? AND status = 'PENDENTE' FOR UPDATE"); $stmt->execute([$requestId]); $request = $stmt->fetch();
        if (!$request) throw new RuntimeException('Solicitação não encontrada.');
        if ((int)$request['requested_by'] === (int)$user['id']) throw new RuntimeException('Aprovação deve ser feita por outro usuário autorizado.');
        $db->prepare("UPDATE deletion_requests SET status='APROVADO', approved_by=?, approved_at=NOW() WHERE id=?")->execute([$user['id'], $requestId]);
        $db->prepare('UPDATE items SET active = 0 WHERE id = ?')->execute([$request['item_id']]);
        audit($db, 'Aprovou exclusão de item por dupla permissão', $operator);
    } elseif ($action === 'movement') {
        $itemId = requirePositive($data, 'itemId'); $qty = requirePositive($data, 'quantity'); $type = requireText($data, 'type');
        $row = $db->prepare('SELECT id, name, quantity FROM items WHERE id = ? AND active = 1 FOR UPDATE'); $row->execute([$itemId]); $item = $row->fetch();
        if (!$item) throw new RuntimeException('Item não encontrado.');
        $delta = in_array($type, ['Retirada', 'Consumo'], true) ? -$qty : $qty;
        if ($item['quantity'] + $delta < 0) throw new RuntimeException('Saldo insuficiente para esta operação.');
        $db->prepare('UPDATE items SET quantity = quantity + ? WHERE id = ?')->execute([$delta, $itemId]);
        $emailRequested = ($data['emailRequested'] ?? 'NAO') === 'SIM' ? 'SIM' : 'NAO';
        $db->prepare('INSERT INTO stock_movements (item_id, movement_type, quantity, operator_name, condition_note, reason, email_requested) VALUES (?, ?, ?, ?, ?, ?, ?)')->execute([$itemId, $type, $qty, requireText($data, 'responsible'), (string)($data['condition'] ?? ''), (string)($data['reason'] ?? ''), $emailRequested]);
        $movementId = (int)$db->lastInsertId();
        $emailSent = $emailRequested === 'SIM' ? notifyManagers($db, $movementId, "WMS: movimentação {$type}", "Item: {$item['name']}\nQuantidade: {$qty}\nResponsável: " . $data['responsible']) : 'NAO';
        $db->prepare('UPDATE stock_movements SET email_sent = ? WHERE id = ?')->execute([$emailSent, $movementId]);
        audit($db, "Movimentação {$type}: {$qty} un de {$item['name']}", $operator);
    } elseif ($action === 'create_picking') {
        $itemId = requirePositive($data, 'itemId'); $qty = requirePositive($data, 'quantity');
        $item = $db->prepare('SELECT id, name, quantity FROM items WHERE id = ? AND active = 1 FOR UPDATE'); $item->execute([$itemId]); $item = $item->fetch();
        if (!$item || $item['quantity'] < $qty) throw new RuntimeException('Quantidade insuficiente em estoque.');
        $code = 'OP-' . date('Ymd') . '-' . random_int(1000, 9999);
        $db->prepare('INSERT INTO picking_orders (order_code, strategy, item_id, quantity, customer_order, status) VALUES (?, ?, ?, ?, ?, "Pendente Conferência")')->execute([$code, requireText($data, 'strategy'), $itemId, $qty, requireText($data, 'customerOrder')]);
        audit($db, "Gerou ordem de picking {$code}", $operator);
    } elseif ($action === 'complete_picking') {
        $code = requireText($data, 'orderCode');
        $stmt = $db->prepare('SELECT p.*, p.quantity AS order_quantity, i.name, i.quantity AS stock_quantity FROM picking_orders p JOIN items i ON i.id = p.item_id WHERE p.order_code = ? FOR UPDATE'); $stmt->execute([$code]); $order = $stmt->fetch();
        if (!$order || $order['status'] === 'Concluído') throw new RuntimeException('Ordem inválida ou já concluída.');
        if ((int)$order['order_quantity'] > (int)$order['stock_quantity']) throw new RuntimeException('Saldo insuficiente.');
        $db->prepare('UPDATE items SET quantity = quantity - ? WHERE id = ?')->execute([$order['order_quantity'], $order['item_id']]);
        $db->prepare('UPDATE picking_orders SET status = "Concluído", completed_at = NOW() WHERE id = ?')->execute([$order['id']]);
        $db->prepare('INSERT INTO stock_movements (item_id, movement_type, quantity, operator_name, reason) VALUES (?, "Picking", ?, ?, ?)')->execute([$order['item_id'], $order['order_quantity'], $operator, "Ordem {$code} concluída"]);
        audit($db, "Conferência concluída para a ordem {$code}", $operator);
    } elseif ($action === 'fulfillment') {
        $itemId = requirePositive($data, 'itemId'); $qty = requirePositive($data, 'quantity');
        $item = $db->prepare('SELECT id, name, quantity FROM items WHERE id = ? FOR UPDATE'); $item->execute([$itemId]); $item = $item->fetch();
        if (!$item || $item['quantity'] < $qty) throw new RuntimeException('Saldo insuficiente para fulfillment.');
        $invoice = (string)random_int(100000, 999999);
        $db->prepare('UPDATE items SET quantity = quantity - ? WHERE id = ?')->execute([$qty, $itemId]);
        $db->prepare('INSERT INTO fulfillment_orders (client_name, client_cnpj, item_id, quantity, recipient, invoice_number) VALUES (?, ?, ?, ?, ?, ?)')->execute([requireText($data, 'client'), requireText($data, 'cnpj'), $itemId, $qty, requireText($data, 'recipient'), $invoice]);
        $db->prepare('INSERT INTO stock_movements (item_id, movement_type, quantity, operator_name, reason) VALUES (?, "Fulfillment", ?, ?, ?)')->execute([$itemId, $qty, $operator, "NF {$invoice}"]);
        audit($db, "Fulfillment NF {$invoice}: {$qty} un de {$item['name']}", $operator);
        $data['invoice'] = $invoice;
    } elseif ($action === 'inventory_adjustment') {
        $itemId = requirePositive($data, 'itemId'); $counted = requirePositive($data, 'countedQuantity'); $factor = requirePositive($data, 'factor');
        $item = $db->prepare('SELECT id, name, quantity FROM items WHERE id = ? FOR UPDATE'); $item->execute([$itemId]); $item = $item->fetch();
        if (!$item) throw new RuntimeException('Item não encontrado.');
        $newQuantity = $counted * $factor;
        $db->prepare('UPDATE items SET quantity = ? WHERE id = ?')->execute([$newQuantity, $itemId]);
        $db->prepare('INSERT INTO inventory_adjustments (item_id, previous_quantity, counted_quantity, conversion_factor, final_quantity, reason, operator_name) VALUES (?, ?, ?, ?, ?, ?, ?)')->execute([$itemId, $item['quantity'], $counted, $factor, $newQuantity, requireText($data, 'reason'), $operator]);
        audit($db, "Inventário ajustado: {$item['name']} de {$item['quantity']} para {$newQuantity}", $operator);
    } else { respond(['error' => 'Ação desconhecida.'], 404); }
    $db->commit();
    respond(['message' => 'Operação registrada.', 'invoice' => $data['invoice'] ?? null, 'data' => snapshot($db, $user)]);
} catch (Throwable $error) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) $db->rollBack();
    respond(['error' => $error->getMessage()], $error instanceof PDOException ? 500 : 422);
}
