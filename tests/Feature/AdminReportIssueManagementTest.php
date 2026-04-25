<?php

namespace Tests\Feature;

use App\Models\ReportIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AdminReportIssueManagementTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_superadmin_can_bulk_delete_selected_report_issues(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $reporter = User::factory()->create(['role' => 'pegawai']);

        $issues = ReportIssue::factory()->count(3)->create([
            'user_id' => $reporter->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.report-issues.bulk-destroy'), [
            'ids' => [$issues[0]->id, $issues[1]->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('report_issues', ['id' => $issues[0]->id]);
        $this->assertDatabaseMissing('report_issues', ['id' => $issues[1]->id]);
        $this->assertDatabaseHas('report_issues', ['id' => $issues[2]->id]);
    }

    public function test_superadmin_can_delete_all_report_issues(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $reporter = User::factory()->create(['role' => 'pegawai']);

        ReportIssue::factory()->count(3)->create([
            'user_id' => $reporter->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.report-issues.destroy-all'));

        $response->assertRedirect();
        $this->assertDatabaseCount('report_issues', 0);
    }

    public function test_bulk_delete_requires_selected_ids(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->from(route('admin.report-issues.index'))
            ->actingAs($admin)
            ->delete(route('admin.report-issues.bulk-destroy'), [
                'ids' => [],
            ]);

        $response->assertRedirect(route('admin.report-issues.index'));
        $response->assertSessionHasErrors('ids');
    }
}
