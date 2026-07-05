<?php
require_once 'config.php';
header('Content-Type: text/plain; charset=utf-8');

$pdo = getDB();
$row = $pdo->query("SELECT MIN(id) as min_id, MAX(id) as max_id, COUNT(*) as total FROM channels")->fetch(PDO::FETCH_ASSOC);
echo "أصغر ID: " . $row['min_id'] . "\n";
echo "أكبر ID: " . $row['max_id'] . "\n";
echo "العدد الكلي: " . $row['total'] . "\n\n";

echo "=== أول 5 قنوات ===\n";
$rows = $pdo->query("SELECT id, name, stream_url FROM channels ORDER BY id ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "ID: {$r['id']} - {$r['name']} - {$r['stream_url']}\n";
}
