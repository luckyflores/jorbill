<?php

namespace App\Services\Odoo\Live;

use App\Services\Odoo\Contracts\OdooClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Odoo Community integration via JSON-RPC + session auth (/web/dataset/call_kw).
 *
 * Why JSON-RPC + session vs XML-RPC: PHP 8's removed ext-xmlrpc forces a library;
 * JSON-RPC works with Laravel's HTTP facade out of the box. Same capability surface.
 *
 * Auth lifecycle:
 *   1. POST /web/session/authenticate  → uid + session cookie
 *   2. subsequent POST /web/dataset/call_kw  use the cookie
 *
 * Lazy-auth: this->uid is null until first call. testConnection() forces auth.
 */
class LiveOdooClient implements OdooClient
{
    private ?int $uid = null;
    private ?string $sessionId = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $db,
        private readonly string $login,
        private readonly string $password,
        private readonly int $timeout = 15,
    ) {}

    public function id(): string { return 'live'; }

    private function http(): PendingRequest
    {
        $h = Http::baseUrl(rtrim($this->baseUrl, '/'))->timeout($this->timeout)->acceptJson();
        if ($this->sessionId) {
            $h = $h->withCookies(['session_id' => $this->sessionId], parse_url($this->baseUrl, PHP_URL_HOST));
        }
        return $h;
    }

    private function authenticate(): bool
    {
        $r = Http::baseUrl(rtrim($this->baseUrl, '/'))->timeout($this->timeout)->acceptJson()
            ->post('/web/session/authenticate', [
                'jsonrpc' => '2.0',
                'params'  => [
                    'db'       => $this->db,
                    'login'    => $this->login,
                    'password' => $this->password,
                ],
            ]);

        if (! $r->successful()) {
            Log::error('LiveOdooClient::authenticate HTTP error', ['status' => $r->status()]);
            return false;
        }

        $body = $r->json() ?? [];
        $uid = $body['result']['uid'] ?? null;
        if (! $uid) {
            Log::warning('LiveOdooClient::authenticate failed', ['result' => $body['result'] ?? null]);
            return false;
        }
        $this->uid = (int) $uid;

        // Odoo returns the session cookie in Set-Cookie; parse from headers
        $setCookie = $r->header('Set-Cookie');
        if (preg_match('/session_id=([^;]+)/', $setCookie ?? '', $m)) {
            $this->sessionId = $m[1];
        }
        return true;
    }

    private function ensureAuth(): bool
    {
        return $this->uid !== null || $this->authenticate();
    }

    public function testConnection(): array
    {
        try {
            if (! $this->authenticate()) {
                return ['ok' => false, 'uid' => null, 'server_version' => null, 'error' => 'authentication failed'];
            }
            // Get server version via /web/webclient/version_info
            $r = Http::baseUrl(rtrim($this->baseUrl, '/'))->timeout($this->timeout)
                ->post('/web/webclient/version_info', ['jsonrpc'=>'2.0', 'params'=>[]]);
            $version = $r->json('result.server_version') ?? 'unknown';
            return ['ok' => true, 'uid' => $this->uid, 'server_version' => $version, 'error' => null];
        } catch (Throwable $e) {
            return ['ok' => false, 'uid' => null, 'server_version' => null, 'error' => $e->getMessage()];
        }
    }

    /** Generic call_kw wrapper */
    private function callKw(string $model, string $method, array $args = [], array $kwargs = []): mixed
    {
        if (! $this->ensureAuth()) return null;
        try {
            $r = $this->http()->post('/web/dataset/call_kw', [
                'jsonrpc' => '2.0',
                'method'  => 'call',
                'params'  => [
                    'model'  => $model,
                    'method' => $method,
                    'args'   => $args,
                    'kwargs' => (object) $kwargs,
                ],
            ]);
            if (! $r->successful()) {
                Log::error('LiveOdooClient::callKw HTTP error', ['model' => $model, 'method' => $method, 'status' => $r->status()]);
                return null;
            }
            $body = $r->json() ?? [];
            if (isset($body['error'])) {
                Log::warning('LiveOdooClient::callKw Odoo error', ['error' => $body['error']]);
                return null;
            }
            return $body['result'] ?? null;
        } catch (Throwable $e) {
            Log::error('LiveOdooClient::callKw threw', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function findOrCreatePartner(array $customer): ?int
    {
        $ref = $customer['customer_code'] ?? null;
        if ($ref) {
            $existing = $this->callKw('res.partner', 'search_read', [[['ref', '=', $ref]]], ['fields' => ['id'], 'limit' => 1]);
            if (! empty($existing[0]['id'])) {
                return (int) $existing[0]['id'];
            }
        }

        $vals = [
            'name'    => $customer['name'] ?? 'Customer',
            'ref'     => $ref,
            'phone'   => $customer['phone'] ?? null,
            'email'   => $customer['email'] ?? null,
            'street'  => $customer['address_line1'] ?? null,
            'city'    => $customer['city'] ?? null,
            'comment' => "JorBill customer #" . ($customer['id'] ?? '?'),
        ];
        $vals = array_filter($vals, fn ($v) => $v !== null);

        $id = $this->callKw('res.partner', 'create', [$vals]);
        return $id ? (int) $id : null;
    }

    public function getPartner(int $id): ?array
    {
        $rows = $this->callKw('res.partner', 'read', [[$id]], ['fields' => ['id','name','ref','phone','email','street','city']]);
        return $rows[0] ?? null;
    }

    public function listPartners(int $limit = 50, int $offset = 0): array
    {
        $rows = $this->callKw('res.partner', 'search_read', [[]], [
            'fields' => ['id', 'name', 'ref', 'phone', 'email'],
            'limit'  => $limit,
            'offset' => $offset,
            'order'  => 'id desc',
        ]);
        return is_array($rows) ? $rows : [];
    }
}
