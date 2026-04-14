<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BroadcastBatch;
use App\Models\BroadcastRecipient;
use App\Models\Issue;
use App\Models\IssueNote;
use App\Models\School;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Area 1 — Tenant Isolation Tests
 * 
 * Failure consequence: Cross-school data breach
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected School $schoolA;
    protected School $schoolB;
    protected Branch $branchA;
    protected Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create two tenants with schools and branches
        $this->tenantA = Tenant::factory()->withDomain('tenant-a.test')->create();
        $this->schoolA = School::factory()->forTenant($this->tenantA->id)->create();
        $this->branchA = Branch::factory()->forTenantAndSchool($this->tenantA->id, $this->schoolA->id)->create();

        $this->tenantB = Tenant::factory()->withDomain('tenant-b.test')->create();
        $this->schoolB = School::factory()->forTenant($this->tenantB->id)->create();
        $this->branchB = Branch::factory()->forTenantAndSchool($this->tenantB->id, $this->schoolB->id)->create();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    /**
     * Test 1.1: Issue query only returns current tenant records
     * 
     * Verifies that BelongsToTenant on Issue filters by active tenant
     */
    public function test_issue_query_only_returns_current_tenant_records(): void
    {
        // Initialize Tenant A and create 2 issues
        tenancy()->initialize($this->tenantA);
        Issue::factory()
            ->forTenantSchoolBranch($this->tenantA->id, $this->schoolA->id, $this->branchA->id)
            ->count(2)
            ->create();
        tenancy()->end();

        // Initialize Tenant B and create 1 issue
        tenancy()->initialize($this->tenantB);
        Issue::factory()
            ->forTenantSchoolBranch($this->tenantB->id, $this->schoolB->id, $this->branchB->id)
            ->create();

        // Query should only return Tenant B's issue
        $issues = Issue::all();

        $this->assertCount(1, $issues);
        $this->assertEquals($this->tenantB->id, $issues->first()->tenant_id);
    }

    /**
     * Test 1.2: BroadcastRecipient global scope isolates tenants
     * 
     * Verifies that BroadcastRecipient (newly scoped) isolates by tenant
     */
    public function test_broadcast_recipient_global_scope_isolates_tenants(): void
    {
        // Initialize Tenant A and create 3 recipients
        tenancy()->initialize($this->tenantA);
        $batchA = BroadcastBatch::factory()->forTenant($this->tenantA->id)->create();
        BroadcastRecipient::factory()
            ->forTenantAndBatch($this->tenantA->id, $batchA->id)
            ->count(3)
            ->create();
        tenancy()->end();

        // Initialize Tenant B and create 1 recipient
        tenancy()->initialize($this->tenantB);
        $batchB = BroadcastBatch::factory()->forTenant($this->tenantB->id)->create();
        BroadcastRecipient::factory()
            ->forTenantAndBatch($this->tenantB->id, $batchB->id)
            ->create();

        // Query should only return Tenant B's recipient
        $recipients = BroadcastRecipient::all();

        $this->assertCount(1, $recipients);
        $this->assertEquals($this->tenantB->id, $recipients->first()->tenant_id);
    }

    /**
     * Test 1.3: IssueNote global scope isolates tenants
     * 
     * Verifies that IssueNote with BelongsToTenant
     */
    public function test_issue_note_global_scope_isolates_tenants(): void
    {
        // Initialize Tenant A and create a note
        tenancy()->initialize($this->tenantA);
        $issueA = Issue::factory()
            ->forTenantSchoolBranch($this->tenantA->id, $this->schoolA->id, $this->branchA->id)
            ->create();
        $userA = User::factory()->create(['tenant_id' => $this->tenantA->id]);
        IssueNote::factory()
            ->forTenantAndIssue($this->tenantA->id, $issueA->id)
            ->create(['user_id' => $userA->id]);
        tenancy()->end();

        // Initialize Tenant B and create a note
        tenancy()->initialize($this->tenantB);
        $issueB = Issue::factory()
            ->forTenantSchoolBranch($this->tenantB->id, $this->schoolB->id, $this->branchB->id)
            ->create();
        $userB = User::factory()->create(['tenant_id' => $this->tenantB->id]);
        IssueNote::factory()
            ->forTenantAndIssue($this->tenantB->id, $issueB->id)
            ->create(['user_id' => $userB->id]);

        // Query should only return Tenant B's note
        $notes = IssueNote::all();

        $this->assertCount(1, $notes);
        $this->assertEquals($this->tenantB->id, $notes->first()->tenant_id);
    }

    /**
     * Test 1.4: Workflow controller aborts if issue belongs to different tenant
     * 
     * Verifies: abort_unless($issue->tenant_id === tenant('id'), 404)
     * 
     * Note: This test requires the WorkflowController to be implemented with tenant checks
     */
    public function test_workflow_controller_aborts_if_issue_belongs_to_different_tenant(): void
    {
        // Create issue in Tenant A
        tenancy()->initialize($this->tenantA);
        $issueA = Issue::factory()
            ->forTenantSchoolBranch($this->tenantA->id, $this->schoolA->id, $this->branchA->id)
            ->create();
        $issueAId = $issueA->id;
        tenancy()->end();

        // Initialize Tenant B and create a staff user
        tenancy()->initialize($this->tenantB);
        /** @var User $userB */
        $userB = User::factory()->create(['tenant_id' => $this->tenantB->id]);

        // Tenant B staff tries to update Tenant A issue status
        $response = $this->actingAs($userB, 'web')
            ->post("/admin/issues/{$issueAId}/status", [
                'status' => 'in_progress',
            ]);

        // Should return 404 (not found in tenant context)
        $response->assertStatus(404);
    }

    /**
     * Test 1.5: Attachment controller aborts if attachment belongs to different tenant
     * 
     * Verifies: AttachmentController tenant check
     * 
     * Note: This test requires signed URL functionality and AttachmentController implementation
     */
    public function test_attachment_controller_aborts_if_attachment_belongs_to_different_tenant(): void
    {
        $this->markTestSkipped('Requires AttachmentController and IssueAttachment factory implementation');

        // This test would verify:
        // 1. Create attachment in Tenant A with signed URL
        // 2. Try to access from Tenant B context
        // 3. Should return 404
    }
}
