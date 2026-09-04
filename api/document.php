<?php
declare(strict_types=1);

require __DIR__ . '/database.php';
require __DIR__ . '/auth_lib.php';
header('Content-Type: application/json; charset=utf-8');

function reply(array $body, int $status = 200): never {
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    requireAuthenticated();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') reply(['error' => 'Método não permitido.'], 405);
    $itemId = filter_input(INPUT_POST, 'itemId', FILTER_VALIDATE_INT);
    $file = $_FILES['documento'] ?? null;
    if (!$itemId || !$file || $file['error'] !== UPLOAD_ERR_OK) reply(['error' => 'Documento não recebido.'], 422);
    if ($file['size'] > 5 * 1024 * 1024) reply(['error' => 'O documento deve ter no máximo 5 MB.'], 422);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($mime, $allowed, true)) reply(['error' => 'Envie PDF, JPG ou PNG.'], 422);
    $contents = file_get_contents($file['tmp_name']);
    $db = database();
    $exists = $db->prepare('SELECT id FROM items WHERE id = ?'); $exists->execute([$itemId]);
    if (!$exists->fetch()) reply(['error' => 'Ativo não encontrado.'], 404);
    $stmt = $db->prepare('INSERT INTO item_documents (item_id, file_name, mime_type, file_size, file_data) VALUES (?, ?, ?, ?, ?)');
    $stmt->bindValue(1, $itemId, PDO::PARAM_INT);
    $stmt->bindValue(2, basename($file['name']));
    $stmt->bindValue(3, $mime);
    $stmt->bindValue(4, $file['size'], PDO::PARAM_INT);
    $stmt->bindValue(5, $contents, PDO::PARAM_LOB);
    $stmt->execute();
    reply(['message' => 'Documento armazenado com segurança.'], 201);
} catch (Throwable $error) {
    reply(['error' => 'Não foi possível armazenar o documento.'], 500);
}
