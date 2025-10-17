<?php

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

const STORAGE_DIR = __DIR__ . '/data';
const STORAGE_FILE = STORAGE_DIR . '/entries.json';

if (!is_dir(STORAGE_DIR)) {
    mkdir(STORAGE_DIR, 0775, true);
}

/**
 * @return array<int, array<string, mixed>>
 */
function read_entries(): array
{
    if (!file_exists(STORAGE_FILE)) {
        return [];
    }

    $contents = file_get_contents(STORAGE_FILE);
    if ($contents === false || $contents === '') {
        return [];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return [];
    }

    return array_values(array_filter(
        $decoded,
        static fn($entry) => is_array($entry) && isset($entry['id'])
    ));
}

/**
 * @param array<int, array<string, mixed>> $entries
 */
function write_entries(array $entries): void
{
    $json = json_encode($entries, JSON_PRETTY_PRINT);
    if ($json === false) {
        throw new RuntimeException('Failed to encode entries');
    }

    $tmpFile = STORAGE_FILE . '.tmp';
    file_put_contents($tmpFile, $json, LOCK_EX);
    rename($tmpFile, STORAGE_FILE);
}

function respond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function get_json_input(): array
{
    $input = file_get_contents('php://input');
    if ($input === false || $input === '') {
        return [];
    }

    $decoded = json_decode($input, true);
    if (!is_array($decoded)) {
        respond(['error' => 'Invalid JSON payload'], 400);
    }

    return $decoded;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    switch ($method) {
        case 'GET':
            $entries = read_entries();
            respond(['entries' => $entries]);

        case 'POST':
            $payload = get_json_input();
            $systolic = isset($payload['systolic']) ? (int) $payload['systolic'] : null;
            $diastolic = isset($payload['diastolic']) ? (int) $payload['diastolic'] : null;
            $notes = isset($payload['notes']) ? trim((string) $payload['notes']) : '';

            if ($systolic <= 0 || $diastolic <= 0) {
                respond(['error' => 'Systolic and diastolic readings must be positive numbers.'], 422);
            }

            $entries = read_entries();
            $entries[] = [
                'id' => bin2hex(random_bytes(8)),
                'systolic' => $systolic,
                'diastolic' => $diastolic,
                'notes' => $notes,
                'timestamp' => time(),
            ];

            write_entries($entries);
            respond(['entry' => end($entries)], 201);

        case 'PUT':
            $payload = get_json_input();
            $id = isset($payload['id']) ? (string) $payload['id'] : '';
            $systolic = isset($payload['systolic']) ? (int) $payload['systolic'] : null;
            $diastolic = isset($payload['diastolic']) ? (int) $payload['diastolic'] : null;
            $notes = isset($payload['notes']) ? trim((string) $payload['notes']) : '';

            if ($id === '') {
                respond(['error' => 'Missing entry identifier.'], 400);
            }

            if ($systolic <= 0 || $diastolic <= 0) {
                respond(['error' => 'Systolic and diastolic readings must be positive numbers.'], 422);
            }

            $entries = read_entries();
            $updated = false;

            foreach ($entries as &$entry) {
                if (($entry['id'] ?? null) === $id) {
                    $entry['systolic'] = $systolic;
                    $entry['diastolic'] = $diastolic;
                    $entry['notes'] = $notes;
                    $entry['timestamp'] = time();
                    $updated = true;
                    break;
                }
            }
            unset($entry);

            if (!$updated) {
                respond(['error' => 'Entry not found.'], 404);
            }

            write_entries($entries);
            respond(['entries' => $entries]);

        case 'DELETE':
            $entries = [];
            write_entries($entries);
            respond(['entries' => $entries]);

        default:
            respond(['error' => 'Method not allowed'], 405);
    }
} catch (Throwable $exception) {
    respond(['error' => $exception->getMessage()], 500);
}
