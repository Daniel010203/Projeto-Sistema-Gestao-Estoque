<?php
declare(strict_types=1);
require __DIR__ . '/auth_lib.php';

function pdfText(string $text): string { return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], iconv('UTF-8', 'Windows-1252//TRANSLIT', $text)); }
function simplePdf(array $lines): string {
    $content = "BT\n/F1 18 Tf\n50 790 Td\n";
    foreach ($lines as $index => $line) { if ($index > 0) $content .= "0 -19 Td\n"; $content .= '(' . pdfText($line) . ") Tj\n"; }
    $content .= "ET";
    $objects = ["<< /Type /Catalog /Pages 2 0 R >>", "<< /Type /Pages /Kids [3 0 R] /Count 1 >>", "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>", "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>", "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream"];
    $pdf = "%PDF-1.4\n"; $offsets = [0];
    foreach ($objects as $i => $object) { $offsets[] = strlen($pdf); $pdf .= ($i + 1) . " 0 obj\n{$object}\nendobj\n"; }
    $xref = strlen($pdf); $pdf .= "xref\n0 6\n0000000000 65535 f \n"; for ($i=1;$i<=5;$i++) $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
    return $pdf . "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
}

$user = requireAuthenticated(); $db = database();
$period = $_GET['period'] ?? 'daily';
$module = preg_replace('/[^a-z_]/', '', $_GET['module'] ?? 'estoque');
$days = ['daily'=>1, 'weekly'=>7, 'monthly'=>30, 'annual'=>365][$period] ?? 1;
$since = (new DateTimeImmutable("-{$days} days"))->format('Y-m-d H:i:s');
$summary = $db->query('SELECT COUNT(*) total_items, COALESCE(SUM(quantity),0) total_quantity, COALESCE(SUM(quantity <= minimum_stock),0) critical_items FROM items WHERE active = 1')->fetch();
$stmt = $db->prepare('SELECT movement_type, COUNT(*) total, COALESCE(SUM(quantity),0) quantity FROM stock_movements WHERE created_at >= ? GROUP BY movement_type ORDER BY total DESC'); $stmt->execute([$since]); $movements = $stmt->fetchAll();
$lines = ['E-GESTAO WMS | RELATORIO ' . strtoupper($module) . ' - ' . strtoupper($period), 'Periodo: ' . (new DateTimeImmutable($since))->format('d/m/Y') . ' a ' . date('d/m/Y'), 'Emitido por: ' . $user['name'], '', 'Indicadores de estoque', 'Itens ativos: ' . $summary['total_items'], 'Unidades em estoque: ' . $summary['total_quantity'], 'Itens em estoque critico: ' . $summary['critical_items'], '', 'Movimentacoes no periodo'];
foreach ($movements as $row) $lines[] = $row['movement_type'] . ': ' . $row['total'] . ' registros | ' . $row['quantity'] . ' unidades';
header('Content-Type: application/pdf'); header('Content-Disposition: attachment; filename="relatorio-wms-' . $period . '-' . date('Ymd') . '.pdf"'); header('Content-Length: ' . strlen($pdf = simplePdf($lines))); echo $pdf;
