<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\ReportIssue;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSecurityAndFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_superadmin_cannot_access_admin_only_routes(): void
    {
        $pegawai = User::factory()->create(['role' => 'pegawai']);

        $this->actingAs($pegawai)->get(route('employees.index'))->assertForbidden();
        $this->actingAs($pegawai)->get(route('audit.index'))->assertForbidden();
        $this->actingAs($pegawai)->get(route('dashboard.export.pdf'))->assertForbidden();
    }

    public function test_report_issues_can_be_filtered_by_status_and_work_unit(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $targetUnit = WorkUnit::create(['name' => 'Target Unit', 'slug' => 'target-unit']);
        $otherUnit = WorkUnit::create(['name' => 'Other Unit', 'slug' => 'other-unit']);

        $targetReporter = User::factory()->create(['role' => 'pegawai', 'name' => 'Reporter Target']);
        Employee::factory()->create([
            'user_id' => $targetReporter->id,
            'full_name' => 'Reporter Target',
            'work_unit_id' => $targetUnit->id,
            'nip' => '100000000000000001',
        ]);

        $otherReporter = User::factory()->create(['role' => 'pegawai', 'name' => 'Reporter Other']);
        Employee::factory()->create([
            'user_id' => $otherReporter->id,
            'full_name' => 'Reporter Other',
            'work_unit_id' => $otherUnit->id,
            'nip' => '100000000000000002',
        ]);

        ReportIssue::factory()->create([
            'user_id' => $targetReporter->id,
            'subject' => 'Printer target bermasalah',
            'status' => 'open',
        ]);

        ReportIssue::factory()->create([
            'user_id' => $otherReporter->id,
            'subject' => 'Jaringan unit lain terganggu',
            'status' => 'resolved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.report-issues.index', [
            'status' => 'open',
            'work_unit_id' => $targetUnit->id,
        ]));

        $response->assertOk();
        $response->assertSeeText('Printer target bermasalah');
        $response->assertDontSeeText('Jaringan unit lain terganggu');
    }

    public function test_audit_logs_can_be_filtered_by_activity_and_user(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin', 'name' => 'Admin Utama']);
        $otherUser = User::factory()->create(['role' => 'pegawai', 'name' => 'Pegawai Lain']);

        AuditLog::factory()->create([
            'user_id' => $admin->id,
            'activity' => 'update_settings',
            'details' => 'Admin Utama memperbarui konfigurasi sistem',
        ]);

        AuditLog::factory()->create([
            'user_id' => $otherUser->id,
            'activity' => 'download_document',
            'details' => 'Pegawai Lain mengunduh dokumen',
        ]);

        $response = $this->actingAs($admin)->get(route('audit.index', [
            'activity' => 'update_settings',
            'user_id' => $admin->id,
        ]));

        $response->assertOk();
        $response->assertSeeText('Admin Utama memperbarui konfigurasi sistem');
        $response->assertDontSeeText('Pegawai Lain mengunduh dokumen');
    }
}
