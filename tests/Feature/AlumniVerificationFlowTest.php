<?php

namespace Tests\Feature;

use App\Mail\AlumniPasswordResetMail;
use App\Mail\AlumniRegistrationApprovedMail;
use App\Mail\AlumniRegistrationRejectedMail;
use App\Mail\AlumniRegistrationSubmittedMail;
use App\Models\AlumniRegistration;
use App\Models\ProgramStudy;
use App\Models\Service;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserType;
use App\Services\BridgeApiService;
use Database\Seeders\ProgramStudySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Database\Seeders\ServiceUnitSeeder;
use Database\Seeders\StatusSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\UserTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class AlumniVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected ProgramStudy $prodi;
    protected User $admin;
    protected User $superAdmin;
    protected User $staff;
    protected User $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            UserTypeSeeder::class,
            ProgramStudySeeder::class,
            ServiceUnitSeeder::class,
            StatusSeeder::class,
            ServiceCatalogSeeder::class,
            UserSeeder::class,
        ]);

        $this->prodi = ProgramStudy::active()->firstOrFail();
        $this->admin = User::where('email', 'admin@sipala.ac.id')->firstOrFail();
        $this->superAdmin = User::where('email', 'pustikom@sipala.ac.id')->firstOrFail();
        $this->staff = User::where('email', 'staff1@sipala.ac.id')->firstOrFail();
        $this->mahasiswa = User::where('email', 'mahasiswa@sipala.ac.id')->firstOrFail();
    }

    protected function mockBridgeFailure(string $errorType = 'INVALID_CREDENTIALS'): void
    {
        $mock = Mockery::mock(BridgeApiService::class);
        $mock->shouldReceive('authenticate')->andReturn([
            'success' => false,
            'error_type' => $errorType,
            'message' => 'Invalid credentials',
            'data' => null,
        ]);
        $mock->shouldReceive('logout')->andReturn(true);
        $this->app->instance(BridgeApiService::class, $mock);
    }

    public function test_alumni_self_registration_stores_private_proof_and_sends_confirmation_email(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Mail::fake();

        $response = $this->post(route('alumni.register.submit'), [
            'nama' => 'Siti Alumni Test',
            'nim' => '2108102029',
            'program_study_id' => $this->prodi->id,
            'tahun_lulus' => 2024,
            'email' => 'Siti.Alumni@Example.com',
            'no_hp' => '081234567890',
            'proof_file' => UploadedFile::fake()->create('ijazah.pdf', 300, 'application/pdf'),
            'password' => 'Rahasia123!',
            'password_confirmation' => 'Rahasia123!',
        ]);

        $response->assertRedirect(route('alumni.status'));

        $registration = AlumniRegistration::where('nim', '2108102029')->first();
        $this->assertNotNull($registration);
        $this->assertSame('siti.alumni@example.com', $registration->email);
        $this->assertSame(AlumniRegistration::STATUS_PENDING, $registration->status);
        $this->assertNull(User::where('nomor_identitas', '2108102029')->first());

        Storage::disk('local')->assertExists($registration->proof_file_path);
        Storage::disk('public')->assertMissing($registration->proof_file_path);

        Mail::assertSent(AlumniRegistrationSubmittedMail::class, function ($mail) {
            return $mail->hasTo('siti.alumni@example.com');
        });
    }

    public function test_registration_blocks_duplicate_pending_and_staff_nim_takeover(): void
    {
        Storage::fake('local');
        Mail::fake();

        AlumniRegistration::create([
            'nama' => 'Pending User',
            'nim' => '2108102099',
            'email' => 'pending@example.com',
            'no_hp' => '081200000001',
            'program_study_id' => $this->prodi->id,
            'tahun_lulus' => 2023,
            'proof_file_path' => 'alumni-proofs/test.pdf',
            'proof_original_name' => 'test.pdf',
            'password' => Hash::make('Rahasia123!'),
            'status' => AlumniRegistration::STATUS_PENDING,
        ]);

        // Duplikat NIM PENDING
        $dupResponse = $this->post(route('alumni.register.submit'), [
            'nama' => 'Duplicate NIM',
            'nim' => '2108102099',
            'program_study_id' => $this->prodi->id,
            'tahun_lulus' => 2023,
            'email' => 'lain@example.com',
            'no_hp' => '081200000002',
            'proof_file' => UploadedFile::fake()->image('skl.jpg'),
            'password' => 'Rahasia123!',
            'password_confirmation' => 'Rahasia123!',
        ]);
        $dupResponse->assertSessionHasErrors('nim');

        // Percobaan mengklaim NIP staf/admin
        $takeoverResponse = $this->post(route('alumni.register.submit'), [
            'nama' => 'Fake Admin',
            'nim' => 'ADM001',
            'program_study_id' => $this->prodi->id,
            'tahun_lulus' => 2023,
            'email' => 'attacker@example.com',
            'no_hp' => '081200000003',
            'proof_file' => UploadedFile::fake()->image('skl.jpg'),
            'password' => 'Rahasia123!',
            'password_confirmation' => 'Rahasia123!',
        ]);
        $takeoverResponse->assertSessionHasErrors('nim');
    }

    public function test_pending_alumni_cannot_login_and_receives_clear_pending_message(): void
    {
        $this->mockBridgeFailure('INVALID_CREDENTIALS');

        AlumniRegistration::create([
            'nama' => 'Pending Alumni',
            'nim' => '2108102088',
            'email' => 'pending.login@example.com',
            'no_hp' => '081234560000',
            'program_study_id' => $this->prodi->id,
            'tahun_lulus' => 2024,
            'proof_file_path' => 'alumni-proofs/test.pdf',
            'proof_original_name' => 'test.pdf',
            'password' => Hash::make('Rahasia123!'),
            'status' => AlumniRegistration::STATUS_PENDING,
        ]);

        $response = $this->post(route('login.submit'), [
            'login' => 'pending.login@example.com',
            'password' => 'Rahasia123!',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertStringContainsString('menunggu verifikasi Admin', session('errors')->first('login'));
        $this->assertGuest();
    }

    public function test_admin_can_approve_new_alumni_and_alumni_can_login_with_email_or_nim(): void
    {
        Storage::fake('local');
        Mail::fake();

        Storage::disk('local')->put('alumni-proofs/2026/11/ijazah-test.pdf', 'dummy-pdf-content');

        $registration = AlumniRegistration::create([
            'nama' => 'Doni Alumni Baru',
            'nim' => '2108102029',
            'email' => 'doni.alumni@example.com',
            'no_hp' => '081299998888',
            'program_study_id' => $this->prodi->id,
            'tahun_lulus' => 2025,
            'proof_file_path' => 'alumni-proofs/2026/11/ijazah-test.pdf',
            'proof_original_name' => 'ijazah-test.pdf',
            'proof_mime_type' => 'application/pdf',
            'password' => Hash::make('Rahasia123!'),
            'status' => AlumniRegistration::STATUS_PENDING,
        ]);

        // Non-admin tidak boleh mengakses verifikasi atau bukti privat
        $this->actingAs($this->mahasiswa)->get(route('admin.alumni.index'))->assertStatus(403);
        $this->actingAs($this->mahasiswa)->get(route('admin.alumni.proof', $registration))->assertStatus(403);
        $this->actingAs($this->staff)->post(route('admin.alumni.approve', $registration))->assertStatus(403);

        // Admin dapat melihat sidebar menu, bukti privat, dan menyetujui
        $this->actingAs($this->admin)->get(route('admin.alumni.index'))
            ->assertStatus(200)
            ->assertSee('Verifikasi Alumni')
            ->assertSee('Doni Alumni Baru');

        $this->actingAs($this->admin)->get(route('admin.alumni.proof', $registration))->assertStatus(200);

        $approveResponse = $this->actingAs($this->admin)->post(route('admin.alumni.approve', $registration));
        $approveResponse->assertRedirect(route('admin.alumni.index', ['tab' => 'pending']));

        $registration->refresh();
        $this->assertSame(AlumniRegistration::STATUS_APPROVED, $registration->status);
        $this->assertSame($this->admin->id, $registration->reviewed_by);

        $alumniUser = User::where('nomor_identitas', '2108102029')->with('userType')->first();
        $this->assertNotNull($alumniUser);
        $this->assertTrue($alumniUser->isAlumni());
        $this->assertTrue($alumniUser->isLocalAuth());
        $this->assertTrue($alumniUser->is_active);
        $this->assertSame(2025, (int) $alumniUser->tahun_lulus);

        Mail::assertSent(AlumniRegistrationApprovedMail::class, fn($m) => $m->hasTo('doni.alumni@example.com'));

        // Logout admin, lalu uji login sebagai alumni lewat Email dan lewat NIM
        $this->post(route('logout'));

        $loginByEmail = $this->post(route('login.submit'), [
            'login' => 'Doni.Alumni@example.com',
            'password' => 'Rahasia123!',
        ]);
        $loginByEmail->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($alumniUser);

        $this->post(route('logout'));

        $loginByNim = $this->post(route('login.submit'), [
            'login' => '2108102029',
            'password' => 'Rahasia123!',
        ]);
        $loginByNim->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($alumniUser);
    }

    public function test_existing_mahasiswa_account_upgrades_cleanly_to_alumni_while_preserving_tickets(): void
    {
        Mail::fake();

        $service = Service::firstOrFail();
        $status = Status::firstOrFail();

        $oldTicket = Ticket::create([
            'ticket_number' => 'SIPALA-2026-9999',
            'user_id' => $this->mahasiswa->id,
            'service_id' => $service->id,
            'category_id' => $service->category_id,
            'program_study_id' => $this->mahasiswa->program_study_id,
            'status_id' => $status->id,
            'subject' => 'Tiket Masa Kuliah',
            'description' => 'Tiket lama sebelum lulus',
            'sla_duration' => 3,
            'sla_unit' => 'hari_kerja',
            'deadline' => now()->addDays(3),
        ]);

        $registration = AlumniRegistration::create([
            'nama' => 'Ahmad Alumni Lulus',
            'nim' => $this->mahasiswa->nomor_identitas, // 20260001
            'email' => 'ahmad.lulus@gmail.com',
            'no_hp' => '081277776666',
            'program_study_id' => $this->prodi->id,
            'tahun_lulus' => 2026,
            'proof_file_path' => 'alumni-proofs/skl.pdf',
            'proof_original_name' => 'skl.pdf',
            'password' => Hash::make('PasswordLulus1!'),
            'status' => AlumniRegistration::STATUS_PENDING,
        ]);

        // Halaman admin menandai NIM ini sebagai eks-mahasiswa
        $this->actingAs($this->admin)->get(route('admin.alumni.index'))
            ->assertStatus(200)
            ->assertSee('Eks-Mahasiswa SIPALA');

        $this->actingAs($this->admin)->post(route('admin.alumni.approve', $registration))
            ->assertRedirect(route('admin.alumni.index', ['tab' => 'pending']));

        $this->mahasiswa->refresh();
        $this->mahasiswa->load('userType');

        $this->assertTrue($this->mahasiswa->isAlumni());
        $this->assertTrue($this->mahasiswa->isLocalAuth());
        $this->assertSame('ahmad.lulus@gmail.com', $this->mahasiswa->email);
        $this->assertSame($this->mahasiswa->id, $oldTicket->fresh()->user_id);

        $this->post(route('logout'));

        $this->post(route('login.submit'), [
            'login' => 'ahmad.lulus@gmail.com',
            'password' => 'PasswordLulus1!',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->mahasiswa);
    }

    public function test_mahasiswa_and_dosen_still_cannot_bypass_bridge_via_local_db(): void
    {
        $this->mockBridgeFailure('INVALID_CREDENTIALS');

        $mhsAttempt = $this->post(route('login.submit'), [
            'login' => $this->mahasiswa->nomor_identitas,
            'password' => 'password', // password DB lokal valid, tapi wajib lewat Bridge!
        ]);

        $mhsAttempt->assertSessionHasErrors('login');
        $this->assertGuest();

        $dosenAttempt = $this->post(route('login.submit'), [
            'login' => '198501012010011001',
            'password' => 'password',
        ]);

        $dosenAttempt->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_admin_can_reject_registration_and_alumni_can_check_status_and_reapply(): void
    {
        Storage::fake('local');
        Mail::fake();

        $registration = AlumniRegistration::create([
            'nama' => 'Rina Alumni',
            'nim' => '2108102077',
            'email' => 'rina.alumni@example.com',
            'no_hp' => '081355554444',
            'program_study_id' => $this->prodi->id,
            'tahun_lulus' => 2024,
            'proof_file_path' => 'alumni-proofs/buram.jpg',
            'proof_original_name' => 'buram.jpg',
            'password' => Hash::make('Rahasia123!'),
            'status' => AlumniRegistration::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.alumni.reject', $registration), [
                'rejection_reason' => 'Foto ijazah buram dan tidak terbaca.',
            ])
            ->assertRedirect(route('admin.alumni.index', ['tab' => 'pending']));

        $registration->refresh();
        $this->assertSame(AlumniRegistration::STATUS_REJECTED, $registration->status);
        $this->assertNull(User::where('nomor_identitas', '2108102077')->first());
        Mail::assertSent(AlumniRegistrationRejectedMail::class, fn($m) => $m->hasTo('rina.alumni@example.com'));

        $this->post(route('logout'));

        // Cek status publik menampilkan alasan penolakan
        $statusCheck = $this->followingRedirects()->post(route('alumni.status.check'), [
            'nim' => '2108102077',
            'email' => 'rina.alumni@example.com',
        ]);
        $statusCheck->assertStatus(200)->assertSee('Foto ijazah buram dan tidak terbaca.');

        // Alumni dapat mendaftar ulang dengan dokumen yang lebih jelas
        $reapply = $this->post(route('alumni.register.submit'), [
            'nama' => 'Rina Alumni',
            'nim' => '2108102077',
            'program_study_id' => $this->prodi->id,
            'tahun_lulus' => 2024,
            'email' => 'rina.alumni@example.com',
            'no_hp' => '081355554444',
            'proof_file' => UploadedFile::fake()->create('ijazah-jelas.pdf', 300, 'application/pdf'),
            'password' => 'Rahasia123!',
            'password_confirmation' => 'Rahasia123!',
        ]);
        $reapply->assertRedirect(route('alumni.status'));
        $this->assertSame(2, AlumniRegistration::where('nim', '2108102077')->count());
    }

    public function test_alumni_only_profile_and_password_change_blocks_mahasiswa_and_dosen(): void
    {
        $alumni = User::where('email', 'alumni@sipala.ac.id')->firstOrFail();

        // Mahasiswa & Dosen tidak boleh mengakses route profil alumni dan tidak melihat menu di sidebar
        $this->actingAs($this->mahasiswa)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertDontSee('Profil &amp; Password', false);

        $this->actingAs($this->mahasiswa)
            ->get(route('user.alumni-profile.edit'))
            ->assertStatus(403);

        // Alumni melihat menu & bisa update kontak + ubah password
        $this->actingAs($alumni)
            ->get(route('user.alumni-profile.edit'))
            ->assertStatus(200)
            ->assertSee('Profil &amp; Password', false);

        $this->actingAs($alumni)
            ->put(route('user.alumni-profile.update'), [
                'email' => 'budi.baru@example.com',
                'no_hp' => '081234567899',
            ])
            ->assertRedirect(route('user.alumni-profile.edit'));

        $alumni->refresh();
        $this->assertSame('budi.baru@example.com', $alumni->email);
        $this->assertSame('081234567899', $alumni->no_hp);

        $this->actingAs($alumni)
            ->put(route('user.alumni-profile.password'), [
                'current_password' => 'password',
                'password' => 'PasswordBaru99!',
                'password_confirmation' => 'PasswordBaru99!',
            ])
            ->assertRedirect(route('user.alumni-profile.edit'));

        $this->assertTrue(Hash::check('PasswordBaru99!', $alumni->fresh()->password));
    }

    public function test_alumni_forgot_and_reset_password_only_works_for_local_alumni(): void
    {
        Mail::fake();

        $alumni = User::where('email', 'alumni@sipala.ac.id')->firstOrFail();

        // Mahasiswa SSO ditolak dari forgot password
        $this->post(route('alumni.password.email'), [
            'email' => $this->mahasiswa->email,
        ])->assertSessionHasErrors('email');
        Mail::assertNothingSent();

        // Alumni lokal berhasil dikirimkan tautan reset password
        $resetTokenCaptured = null;
        $this->post(route('alumni.password.email'), [
            'email' => $alumni->email,
        ])->assertSessionHas('status');

        Mail::assertSent(AlumniPasswordResetMail::class, function ($mail) use (&$resetTokenCaptured) {
            if (preg_match('/reset-password\/([^?]+)/', $mail->resetUrl, $matches)) {
                $resetTokenCaptured = $matches[1];
            }
            return $mail->hasTo('alumni@sipala.ac.id');
        });

        $this->assertNotNull($resetTokenCaptured);

        $this->post(route('alumni.password.update'), [
            'token' => $resetTokenCaptured,
            'email' => $alumni->email,
            'password' => 'ResetBerhasil123!',
            'password_confirmation' => 'ResetBerhasil123!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('ResetBerhasil123!', $alumni->fresh()->password));

        // Login menggunakan password hasil reset
        $this->post(route('login.submit'), [
            'login' => $alumni->email,
            'password' => 'ResetBerhasil123!',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($alumni);
    }

    public function test_admin_can_toggle_and_reset_alumni_account_only_and_cannot_target_non_alumni(): void
    {
        $alumni = User::where('email', 'alumni@sipala.ac.id')->firstOrFail();

        // Anti-IDOR: Admin tidak bisa mengutak-atik akun Mahasiswa lewat endpoint alumni
        $this->actingAs($this->admin)
            ->patch(route('admin.alumni.accounts.toggle', $this->mahasiswa))
            ->assertStatus(403);

        // Toggle nonaktifkan alumni
        $this->actingAs($this->admin)
            ->patch(route('admin.alumni.accounts.toggle', $alumni))
            ->assertRedirect(route('admin.alumni.index', ['tab' => 'accounts']));

        $this->assertFalse($alumni->fresh()->is_active);

        // Admin reset password alumni
        $this->actingAs($this->admin)
            ->post(route('admin.alumni.accounts.reset-password', $alumni), [
                'password' => 'AdminReset123!',
                'password_confirmation' => 'AdminReset123!',
            ])
            ->assertRedirect(route('admin.alumni.index', ['tab' => 'accounts']));

        $this->assertTrue(Hash::check('AdminReset123!', $alumni->fresh()->password));
    }

    public function test_alumni_registration_dropdown_contains_all_48_official_program_studies(): void
    {
        $this->assertSame(48, ProgramStudy::active()->count());

        $response = $this->get(route('alumni.register'));
        $response->assertStatus(200)
            ->assertSee('Prodi Pendidikan Agama Islam (S1)')
            ->assertSee('Prodi Tadris Bahasa Inggris (S1)')
            ->assertSee('Prodi Informatika (S1)')
            ->assertSee('Prodi Bioteknologi (S1)')
            ->assertSee('Prodi Pendidikan Profesi Guru (PPG)')
            ->assertSee('Prodi PJJ Pendidikan Agama Islam (PJJ)')
            ->assertSee('Prodi Hukum Keluarga/Akhwal Syaksiyah (S1)')
            ->assertSee('Prodi PJJ Pendidikan Agama Islam (S2)')
            ->assertSee('Prodi Ekonomi Syariah (S3)');
    }

    public function test_csrf_token_sync_endpoint_and_multi_tab_alumni_login_prevent_419_page_expired(): void
    {
        $alumni = User::where('email', 'alumni@sipala.ac.id')->firstOrFail();

        // 1. Endpoint sinkronisasi token CSRF berfungsi
        $csrfResp = $this->getJson(route('csrf.token'));
        $csrfResp->assertStatus(200)->assertJsonStructure(['token']);

        // 2. Jika di tab lain Admin masih login lalu di tab login pengguna submit kredensial Alumni,
        // sesi otomatis beralih ke Alumni tanpa terblokir middleware guest atau 419
        $loginSwitch = $this->actingAs($this->admin)->post(route('login.submit'), [
            'login' => $alumni->email,
            'password' => 'password',
        ]);
        $loginSwitch->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($alumni);

        // 3. Handler HttpException(419) mengarahkan kembali ke halaman login (bukan layar 419 Page Expired)
        $handler = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);
        $req = \Illuminate\Http\Request::create('/login', 'POST', ['login' => $alumni->email]);
        $req->setLaravelSession($this->app['session']->driver());
        $http419 = new \Symfony\Component\HttpKernel\Exception\HttpException(
            419,
            'CSRF token mismatch.',
            new \Illuminate\Session\TokenMismatchException('CSRF token mismatch.')
        );
        $rendered = $handler->render($req, $http419);
        $this->assertSame(302, $rendered->getStatusCode());
    }

    public function test_bridge_api_v2_exact_payloads_for_mahasiswa_dosen_and_orangtua(): void
    {
        $mock = Mockery::mock(BridgeApiService::class);

        // 1. Payload asli Mahasiswa sesuai Dokumentasi API v2 (Poin 2.2.A & 3.1.A)
        $mock->shouldReceive('authenticate')
            ->with('2281050030', '123')
            ->andReturn([
                'success' => true,
                'data' => [
                    'status' => true,
                    'message' => 'Data Found',
                    'count' => 1,
                    'data' => [
                        'username' => '2281050030',
                        'nama' => 'LANGIT KALA FAJAR',
                        'role' => 'mahasiswa',
                        'token' => '52|token_mhs',
                    ],
                ],
            ]);

        $mock->shouldReceive('getProfil')
            ->with('52|token_mhs')
            ->andReturn([
                'status' => true,
                'message' => 'Data Found',
                'count' => 1,
                'data' => [
                    'nim' => '2281050030',
                    'nama' => 'LANGIT KALA FAJAR',
                    'no_hp' => '08123456789',
                    'email' => 'kalafajar@gmail.com',
                    'email_kampus' => '-',
                    'fakultas_nama' => 'ILMU TARBIYAH DAN KEGURUAN',
                    'program_studi_kode' => '105',
                    'program_studi_nama' => 'TADRIS MATEMATIKA',
                    'jenjang' => 'S1',
                    'status' => 'AKTIF',
                ],
            ]);

        // 2. Payload asli Dosen sesuai Dokumentasi API v2 (Poin 2.2.B & 3.1.B — tanpa field role di profil)
        $mock->shouldReceive('authenticate')
            ->with('198810112011012019', '123')
            ->andReturn([
                'success' => true,
                'data' => [
                    'status' => true,
                    'message' => 'Data Found',
                    'count' => 1,
                    'data' => [
                        'username' => '198810112011012019',
                        'nama' => 'BINTANG HADAR',
                        'role' => 'dosen',
                        'token' => '53|token_dosen',
                    ],
                ],
            ]);

        $mock->shouldReceive('getProfil')
            ->with('53|token_dosen')
            ->andReturn([
                'status' => true,
                'message' => 'Data Found',
                'count' => 1,
                'data' => [
                    'nip' => '198810112011012019',
                    'nidn' => '1234567890',
                    'nama' => 'BINTANG HADAR',
                    'gelar_depan' => '',
                    'gelar_belakang' => 'S.T., M.Kom.',
                    'no_hp' => '081299887766',
                    'fakultas' => 'ILMU TARBIYAH DAN KEGURUAN',
                    'program_studi' => 'S1 - INFORMATIKA',
                    'email_kampus' => 'bintanghadar@uinssc.ac.id',
                    'status' => 'Dosen Tetap',
                ],
            ]);

        // 3. Payload Orang Tua (ditolak dari SIPALA)
        $mock->shouldReceive('authenticate')
            ->with('3214569871236547', '25022005')
            ->andReturn([
                'success' => true,
                'data' => [
                    'status' => true,
                    'data' => [
                        'username' => '3214569871236547',
                        'nama' => 'ORANG TUA',
                        'role' => 'orangtua',
                        'token' => '54|token_ortu',
                    ],
                ],
            ]);

        $mock->shouldReceive('logout')->andReturn(true);
        $this->app->instance(BridgeApiService::class, $mock);

        // Uji login Mahasiswa
        $this->post(route('login.submit'), [
            'login' => '2281050030',
            'password' => '123',
        ])->assertRedirect(route('dashboard'));

        $mhs = User::where('nomor_identitas', '2281050030')->with(['userType', 'programStudy'])->firstOrFail();
        $this->assertTrue($mhs->isMahasiswa());
        $this->assertSame('kalafajar@gmail.com', $mhs->email);
        $this->assertSame('08123456789', $mhs->no_hp);
        $this->assertSame('Tadris Matematika', $mhs->programStudy?->name);

        // Uji login Dosen (langsung dari sesi mahasiswa)
        $this->post(route('login.submit'), [
            'login' => '198810112011012019',
            'password' => '123',
        ])->assertRedirect(route('dashboard'));

        $dosen = User::where('nomor_identitas', '198810112011012019')->with(['userType', 'programStudy'])->firstOrFail();
        $this->assertTrue($dosen->isDosenPegawai());
        $this->assertSame('BINTANG HADAR, S.T., M.Kom.', $dosen->nama);
        $this->assertSame('bintanghadar@uinssc.ac.id', $dosen->email);
        $this->assertSame('081299887766', $dosen->no_hp);
        $this->assertSame('Informatika', $dosen->programStudy?->name);

        // Uji login Orang Tua ditolak
        $this->post(route('logout'));
        $this->post(route('login.submit'), [
            'login' => '3214569871236547',
            'password' => '25022005',
        ])->assertSessionHasErrors('login');
        $this->assertNull(User::where('nomor_identitas', '3214569871236547')->first());
    }
}

