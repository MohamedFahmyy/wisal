<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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

// Get raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    // Also support form-encoded
    $data = $_POST;
}

if (empty($data) || empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing guest name']);
    exit;
}

// Prepare record
$record = [
    'id' => isset($data['id']) ? (string)$data['id'] : (string)(round(microtime(true) * 1000)),
    'created_at' => isset($data['date']) ? $data['date'] : date('c'),
    'name' => trim($data['name']),
    'status' => (isset($data['status']) && $data['status'] === 'declined') ? 'declined' : 'attending',
    'guests' => isset($data['guests']) ? max(1, (int)$data['guests']) : 1,
    'message' => isset($data['message']) ? trim($data['message']) : ''
];

// If declined, guests count is 0
if ($record['status'] === 'declined') {
    $record['guests'] = 0;
}

// Read existing
$existing = [];
if (file_exists($dataFile)) {
    $content = file_get_contents($dataFile);
    $existing = json_decode($content, true) ?: [];
}

// Prepend new record (newest first)
array_unshift($existing, $record);

file_put_contents($dataFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'record' => $record]);
