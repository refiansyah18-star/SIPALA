<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BridgeApiService
 *
 * Service untuk komunikasi dengan Bridge API v2 Portal Kampus (eCampuz).
 * Endpoints:
 *   - POST /api/v2/portal/login  → Login & dapatkan Bearer token
 *   - GET  /api/v2/profil        → Ambil data profil user
 *   - POST /api/v2/logout        → Logout & revoke token
 *
 * @see database/Bridge API v2 (Auth & Profil).json (Postman Collection)
 */
class BridgeApiService
{
    protected string $baseUrl;
    protected int $timeout;
    protected string $v1Token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.bridge.base_url'), '/');
        $this->timeout = (int) config('services.bridge.timeout', 15);
        $this->v1Token = (string) config('services.bridge.v1_token', '');
    }

    /**
     * Autentikasi ke Bridge API dengan status hasil terperinci (membedakan kredensial salah vs koneksi down).
     *
     * @param  string  $username  NIM / NIP / NIK
     * @param  string  $password  Password portal
     * @return array{success: bool, data?: array, error_type?: string, status?: int, message?: string}
     */
    public function authenticate(string $username, string $password): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->accept('application/json')
                ->asForm()
                ->post("{$this->baseUrl}/api/v2/portal/login", [
                    'username' => $username,
                    'password' => $password,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Pastikan payload JSON menyatakan status=true dan memiliki token
                if (is_array($data) && data_get($data, 'status') !== false) {
                    Log::info('Bridge API login berhasil', ['username' => $username]);
                    return [
                        'success' => true,
                        'data'    => $data,
                    ];
                }

                return [
                    'success'    => false,
                    'error_type' => 'INVALID_CREDENTIALS',
                    'status'     => $response->status(),
                    'message'    => data_get($data, 'message', 'NIM/NIP atau kata sandi portal salah.'),
                ];
            }

            $statusCode = $response->status();

            Log::warning('Bridge API login ditolak/gagal', [
                'username' => $username,
                'status'   => $statusCode,
                'body'     => $response->body(),
            ]);

            // HTTP 400, 401, 403, 404, 422: Kredensial salah / ditolak oleh portal kampus
            if (in_array($statusCode, [400, 401, 403, 404, 422], true)) {
                return [
                    'success'    => false,
                    'error_type' => 'INVALID_CREDENTIALS',
                    'status'     => $statusCode,
                    'message'    => 'NIM/NIP atau kata sandi portal salah.',
                ];
            }

            // HTTP 5xx: Server portal mengalami masalah
            return [
                'success'    => false,
                'error_type' => 'SERVER_ERROR',
                'status'     => $statusCode,
                'message'    => 'Layanan autentikasi portal kampus sedang mengalami kendala.',
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Bridge API timeout / connection error', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success'    => false,
                'error_type' => 'CONNECTION_ERROR',
                'message'    => 'Koneksi ke Bridge API portal kampus terputus atau timeout.',
            ];
        } catch (\Exception $e) {
            Log::error('Bridge API login exception', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success'    => false,
                'error_type' => 'CONNECTION_ERROR',
                'message'    => $e->getMessage(),
            ];
        }
    }

    /**
     * Login ke Bridge API (backward compatibility).
     *
     * @param  string  $username  NIM / NIP / NIK
     * @param  string  $password  Password portal
     * @return array|null  Response data dari Bridge API (berisi token), atau null jika gagal
     */
    public function login(string $username, string $password): ?array
    {
        $result = $this->authenticate($username, $password);
        return $result['success'] ? ($result['data'] ?? null) : null;
    }

    /**
     * Ambil profil user dari Bridge API.
     *
     * @param  string  $token  Bearer token dari hasil login
     * @return array|null  Data profil user, atau null jika gagal
     */
    public function getProfil(string $token): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->accept('application/json')
                ->withToken($token)
                ->get("{$this->baseUrl}/api/v2/profil");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Bridge API get profil gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Bridge API get profil error', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Logout dari Bridge API (revoke token).
     *
     * @param  string  $token  Bearer token
     * @return bool  True jika berhasil logout
     */
    public function logout(string $token): bool
    {
        try {
            $response = Http::timeout(3)
                ->accept('application/json')
                ->withToken($token)
                ->post("{$this->baseUrl}/api/v2/logout");

            return $response->successful();

        } catch (\Exception $e) {
            Log::warning('Bridge API logout dilewati/timeout', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    // =============================================
    // BRIDGE API v1 (Master Akademik & KHS)
    // =============================================

    /**
     * Ambil daftar fakultas aktif dari Bridge API v1 (GET /api/fakultas).
     */
    public function getFakultas(): ?array
    {
        return $this->getV1('/api/fakultas');
    }

    /**
     * Ambil daftar program studi dari Bridge API v1 (GET /api/program_studi/{fakultas?}).
     */
    public function getProgramStudi(?int $kodeFakultas = null): ?array
    {
        $endpoint = $kodeFakultas !== null
            ? "/api/program_studi/{$kodeFakultas}"
            : '/api/program_studi';

        return $this->getV1($endpoint);
    }

    /**
     * Ambil data KHS / Transkrip semester mahasiswa dari Bridge API v1
     * (GET /api/akademik/transkrip?nim={nim}&semester={semester}).
     */
    public function getKhs(string $nim, string $semester): ?array
    {
        return $this->getV1('/api/akademik/transkrip', [
            'nim'      => $nim,
            'semester' => $semester,
        ]);
    }

    /**
     * Helper HTTP GET untuk endpoint Bridge API v1 dengan Static Bearer Token.
     */
    protected function getV1(string $path, array $query = []): ?array
    {
        if (trim($this->v1Token) === '') {
            Log::warning('Bridge API v1 token belum dikonfigurasi pada environment.', ['path' => $path]);
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->accept('application/json')
                ->withToken($this->v1Token)
                ->get("{$this->baseUrl}{$path}", $query);

            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json) && data_get($json, 'status') !== false) {
                    return data_get($json, 'data', $json);
                }
            }

            Log::warning('Bridge API v1 request gagal', [
                'path'   => $path,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Bridge API v1 error', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
