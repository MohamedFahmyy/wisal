<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dataFile = __DIR__ . '/../data/responses.json';
$dataDir = dirname($dataFile);

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([], JSON_PRETTY_PRINT));
}

$content = file_get_contents($dataFile);
$records = json_decode($content, true) ?: [];

// Support action=delete or action=clear
$action = isset($_GET['action']) ? $_GET['action'] : '';
$deleteId = isset($_GET['id']) ? (string)$_GET['id'] : '';

if ($action === 'clear') {
    file_put_contents($dataFile, json_encode([], JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'records' => []]);
    exit;
}

if ($action === 'delete' && $deleteId !== '') {
    $filtered = array_values(array_filter($records, function($item) use ($deleteId) {
        return (string)$item['id'] !== $deleteId;
    }));
    file_put_contents($dataFile, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true, 'records' => $filtered]);
    exit;
}

echo json_encode(['success' => true, 'records' => $records]);
