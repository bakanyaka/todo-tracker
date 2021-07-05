<?php

namespace Tests\Feature\Api;

use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageIssuesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_delete_issue()
    {
        $issue = factory(Issue::class)->create();

        $this->signInAsAdmin()->deleteJson(route('api.issues.destroy', $issue))->assertNoContent();

        $this->assertDeleted($issue);
    }

    /** @test */
    public function non_admin_user_can_not_delete_issue()
    {
        $issue = factory(Issue::class)->create();

        $this->signIn()->deleteJson(route('api.issues.destroy', $issue))->assertForbidden();
    }

}
