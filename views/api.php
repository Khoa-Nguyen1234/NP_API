<?php

/**
 * api.php — Endpoint JSON cho AJAX polling
 * Trả về dữ liệu sheet + hash để JS phát hiện thay đổi
 */

define('SPREADSHEET_ID',   '1QIpax_ruAJeZETenKARrlnmfAAIY0eL5oi3H7o578BE');
define('SHEET_RANGE',      'Hoa 12H1 Ca B81!A3:Z1000');
define('CREDENTIALS_FILE', __DIR__ . '/credentials.json');
define('TOKEN_FILE',       __DIR__ . '/token_cache.json');
define('CACHE_DURATION',   30); // Cache 30 giây — khớp polling interval

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// ── Tiện ích ──────────────────────────────────────────
function base64UrlEncode(string $d): string
{
    return rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
}

function httpGet(string $url, string $token): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token"],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $r = curl_exec($ch);
    if (curl_errno($ch)) throw new Exception('cURL: ' . curl_error($ch));
    curl_close($ch);
    return $r;
}

function httpPost(string $url, string $body): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $r = curl_exec($ch);
    if (curl_errno($ch)) throw new Exception('cURL: ' . curl_error($ch));
    curl_close($ch);
    return $r;
}

// ── Access Token ──────────────────────────────────────
function getAccessToken(): string
{
    if (file_exists(TOKEN_FILE)) {
        $c = json_decode(file_get_contents(TOKEN_FILE), true);
        if ($c && $c['expires_at'] > time() + 30) return $c['access_token'];
    }

    if (!file_exists(CREDENTIALS_FILE))
        throw new Exception('Không tìm thấy credentials.json');

    $creds = json_decode(file_get_contents(CREDENTIALS_FILE), true);
    if (!isset($creds['private_key'], $creds['client_email']))
        throw new Exception('credentials.json không hợp lệ');

    $now     = time() - 60;
    $header  = base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $payload = base64UrlEncode(json_encode([
        'iss'   => $creds['client_email'],
        'scope' => 'https://www.googleapis.com/auth/spreadsheets.readonly',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'exp'   => $now + 3600,
        'iat'   => $now,
    ]));

    $signInput = "$header.$payload";
    openssl_sign($signInput, $sig, $creds['private_key'], 'SHA256');
    $jwt = "$signInput." . base64UrlEncode($sig);

    $data = json_decode(httpPost('https://oauth2.googleapis.com/token', http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt,
    ])), true);

    if (empty($data['access_token']))
        throw new Exception('Lỗi token: ' . ($data['error_description'] ?? 'unknown'));

    file_put_contents(TOKEN_FILE, json_encode([
        'access_token' => $data['access_token'],
        'expires_at'   => $now + ($data['expires_in'] ?? 3600),
    ]));

    return $data['access_token'];
}

// ── Sheet Data ────────────────────────────────────────
function getSheetData(): array
{
    $cacheFile = __DIR__ . '/sheet_cache.json';
    $force     = ($_GET['force'] ?? '') === '1';

    if (!$force && file_exists($cacheFile)) {
        $c = json_decode(file_get_contents($cacheFile), true);
        if ($c && $c['cached_at'] > time() - CACHE_DURATION)
            return $c;
    }

    $url = sprintf(
        'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
        urlencode(SPREADSHEET_ID),
        urlencode(SHEET_RANGE)
    );

    $json = json_decode(httpGet($url, getAccessToken()), true);
    if (isset($json['error']))
        throw new Exception('Sheets API: ' . $json['error']['message']);

    $payload = ['cached_at' => time(), 'data' => $json['values'] ?? []];
    file_put_contents($cacheFile, json_encode($payload));
    return $payload;
}

// ── Main ──────────────────────────────────────────────
try {
    $result = getSheetData();
    echo json_encode([
        'ok'        => true,
        'hash'      => md5(json_encode($result['data'])),
        'cached_at' => $result['cached_at'],
        'rows'      => $result['data'],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
