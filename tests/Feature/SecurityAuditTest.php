<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\ServiceRequirement;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Models\UserType;
use App\Services\BridgeApiService;
use Database\Seeders\ProgramStudySeeder;
use Database\Seeders\StatusSeeder;
use Database\Seeders\UserTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserTypeSeeder::class,
            StatusSeeder::class,
            ProgramStudySeeder::class,
        ]);
    }

    protected function makeUser(string $roleName, array $overrides = []): User
    {
        $typeId = UserType::where('name', $roleName)->value('id');

        return User::create(array_merge([
            'user_type_id' => $typeId,
            'nama' => "User {$roleName}",
            'email' => strtolower($roleName) . '.' . uniqid() . '@example.com',
            'nomor_identitas' => 'ID' . random_int(100000, 999999),
            'password' => Hash::make('Password123!'),
            'auth_source' => User::AUTH_SOURCE_LOCAL,
            'is_active' => true,
        ], $overrides));
    }

    protected function makeServiceForRole(string $roleName): Service
    {
        $category = Category::create([
            'name' => 'Kategori Uji ' . uniqid(),
            'description' => 'Deskripsi kategori',
            'icon' => 'folder',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $service = Service::create([
            'category_id' => $category->id,
            'name' => 'Layanan Uji ' . uniqid(),
            'description' => 'Deskripsi layanan',
            'sla_duration' => 3,
            'sla_unit' => 'hari_kerja',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $typeId = UserType::where('name', $roleName)->value('id');
        $service->targets()->create(['user_type_id' => $typeId]);

        return $service;
    }

    public function test_mime_spoofed_html_file_renamed_to_jpg_is_rejected_on_ticket_submission(): void
    {
        Storage::fake('local');
        Mail::fake();

        $mahasiswa = $this->makeUser(UserType::MAHASISWA);
        $service = $this->makeServiceForRole(UserType::MAHASISWA);

        // File berisi HTML/XSS namun berekstensi .jpg
        $spoofedFile = UploadedFile::fake()->createWithContent(
            'nota.jpg',
            '<html><body><script>alert("xss")</script></body></html>'
        );

        $response = $this->actingAs($mahasiswa)->post(route('user.tickets.store'), [
            'service_id' => $service->id,
            'subject' => 'Uji Keamanan Upload',
            'description' => 'Deskripsi minimal sepuluh karakter untuk uji keamanan upload berkas.',
            'attachments' => [$spoofedFile],
        ]);

        $response->assertSessionHasErrors('attachments.0');
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_cannot_bypass_second_required_document_by_uploading_only_first_requirement(): void
    {
        Storage::fake('local');
        Mail::fake();

        $mahasiswa = $this->makeUser(UserType::MAHASISWA);
        $service = $this->makeServiceForRole(UserType::MAHASISWA);

        $req1 = ServiceRequirement::create([
            'service_id' => $service->id,
            'name' => 'Scan KTM',
            'is_required' => true,
            'allowed_file_types' => 'pdf,jpg,png',
            'max_file_size' => 2048,
            'max_file_count' => 1,
            'sort_order' => 1,
        ]);

        $req2 = ServiceRequirement::create([
            'service_id' => $service->id,
            'name' => 'Transkrip Nilai',
            'is_required' => true,
            'allowed_file_types' => 'pdf,jpg,png',
            'max_file_size' => 2048,
            'max_file_count' => 1,
            'sort_order' => 2,
        ]);

        $validPdf = UploadedFile::fake()->createWithContent('ktm.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        $response = $this->actingAs($mahasiswa)->post(route('user.tickets.store'), [
            'service_id' => $service->id,
            'subject' => 'Uji Syarat Wajib Ganda',
            'description' => 'Deskripsi minimal sepuluh karakter untuk uji validasi syarat dokumen.',
            'attachments' => [
                $req1->id => $validPdf,
            ],
        ]);

        $response->assertSessionHasErrors("attachments.{$req2->id}");
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_attachment_endpoint_enforces_idor_and_sets_nosniff_and_csp_headers(): void
    {
        Storage::fake('local');

        $owner = $this->makeUser(UserType::MAHASISWA);
        $otherUser = $this->makeUser(UserType::MAHASISWA);
        $service = $this->makeServiceForRole(UserType::MAHASISWA);
        $status = Status::where('name', Status::DIAJUKAN)->firstOrFail();

        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateTicketNumber(),
            'user_id' => $owner->id,
            'service_id' => $service->id,
            'category_id' => $service->category_id,
            'status_id' => $status->id,
            'subject' => 'Uji IDOR Lampiran',
            'description' => 'Deskripsi uji IDOR lampiran tiket.',
            'sla_duration' => 3,
            'sla_unit' => 'hari_kerja',
            'deadline' => now()->addDays(3),
        ]);

        $filePath = 'attachments/2026/11/sample.pdf';
        Storage::disk('local')->put($filePath, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        $attachment = TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'original_name' => 'sample.pdf',
            'stored_name' => 'sample.pdf',
            'file_path' => $filePath,
            'file_size' => 50,
            'mime_type' => 'application/pdf',
        ]);

        // Pengguna lain harus ditolak 403 (Anti-IDOR)
        $this->actingAs($otherUser)
            ->get(route('tickets.attachments.show', [$ticket, $attachment]))
            ->assertStatus(403);

        // Pemilik sah mendapat 200 beserta header keamanan anti-sniff & CSP
        $ownerResponse = $this->actingAs($owner)
            ->get(route('tickets.attachments.show', [$ticket, $attachment]));

        $ownerResponse->assertStatus(200);
        $ownerResponse->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString("default-src 'none'", (string) $ownerResponse->headers->get('Content-Security-Policy'));
    }

    public function test_deactivated_user_is_immediately_logged_out_from_dashboard_without_redirect_loop(): void
    {
        $alumni = $this->makeUser(UserType::ALUMNI, ['is_active' => true]);

        $this->actingAs($alumni)->get('/dashboard')->assertStatus(200);

        // Admin menonaktifkan akun saat sesi sedang berjalan
        $alumni->update(['is_active' => false]);

        $response = $this->actingAs($alumni->fresh())->get('/dashboard');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_security_headers_and_strict_referrer_policy_on_password_reset_route(): void
    {
        $landing = $this->get('/');
        $landing->assertStatus(200);
        $landing->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $landing->assertHeader('X-Content-Type-Options', 'nosniff');
        $landing->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $resetForm = $this->get('/alumni/reset-password/dummy-secret-token?email=test@example.com');
        $resetForm->assertStatus(200);
        $resetForm->assertHeader('Referrer-Policy', 'no-referrer');
    }

    public function test_deleting_staff_and_admin_with_activity_logs_does_not_throw_500_fk_error(): void
    {
        $superAdmin = $this->makeUser(UserType::SUPER_ADMIN);
        $adminWithLogs = $this->makeUser(UserType::ADMIN);
        $staffWithLogs = $this->makeUser(UserType::STAFF);
        $freshAdmin = $this->makeUser(UserType::ADMIN);
        $freshStaff = $this->makeUser(UserType::STAFF);

        ActivityLog::log($adminWithLogs->id, ActivityLog::ACTION_LOGIN, ActivityLog::MODULE_AUTH, null, 'Admin login');
        ActivityLog::log($staffWithLogs->id, ActivityLog::ACTION_LOGIN, ActivityLog::MODULE_AUTH, null, 'Staff login');

        // Akun dengan riwayat audit dinonaktifkan dengan aman (tanpa 500 FK error)
        $this->actingAs($superAdmin)
            ->delete(route('admin.staff.destroy', $staffWithLogs))
            ->assertRedirect(route('admin.staff.index'))
            ->assertSessionHas('info');
        $this->assertFalse($staffWithLogs->fresh()->is_active);

        $this->actingAs($superAdmin)
            ->delete(route('admin.admins.destroy', $adminWithLogs))
            ->assertRedirect(route('admin.admins.index'))
            ->assertSessionHas('info');
        $this->assertFalse($adminWithLogs->fresh()->is_active);

        // Akun baru tanpa riwayat dapat dihapus permanen
        $this->actingAs($superAdmin)
            ->delete(route('admin.staff.destroy', $freshStaff))
            ->assertRedirect(route('admin.staff.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $freshStaff->id]);

        $this->actingAs($superAdmin)
            ->delete(route('admin.admins.destroy', $freshAdmin))
            ->assertRedirect(route('admin.admins.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $freshAdmin->id]);
    }

    public function test_service_field_options_are_stored_as_array_and_deletable_without_crash(): void
    {
        $admin = $this->makeUser(UserType::ADMIN);
        $service = $this->makeServiceForRole(UserType::MAHASISWA);

        $this->actingAs($admin)->post(route('admin.services.fields.store', $service), [
            'label' => 'Semester',
            'field_type' => 'select',
            'options' => 'Ganjil, Genap',
            'is_required' => '1',
        ])->assertRedirect(route('admin.services.edit', $service));

        $field = ServiceField::where('service_id', $service->id)->firstOrFail();
        $this->assertIsArray($field->options);
        $this->assertSame(['Ganjil', 'Genap'], $field->options);

        $this->actingAs($admin)
            ->delete(route('admin.services.fields.destroy', [$service, $field]))
            ->assertRedirect(route('admin.services.edit', $service))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('service_fields', ['id' => $field->id]);
    }

    public function test_public_webroot_does_not_expose_erd_html_and_v1_token_has_no_hardcoded_secret(): void
    {
        $this->assertFileDoesNotExist(public_path('erd.html'));

        config(['services.bridge.v1_token' => '']);
        $bridge = new BridgeApiService();
        $this->assertNull($bridge->getFakultas());
    }
}
