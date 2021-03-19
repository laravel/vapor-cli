<?php

namespace Laravel\VaporCli\Tests;

use Laravel\VaporCli\Models\Deployment;
use PHPUnit\Framework\TestCase;

class SolutionsTest extends TestCase
{
    public function test_no_solutions()
    {
        $deployment = new Deployment([
            'status_message' => 'Internal error.',
        ]);

        $this->assertCount(0, $deployment->solutions());
    }

    public function test_bucket_name_already_exists()
    {
        $deployment = new Deployment([
            'status_message' => 'This bucket name may already be reserved by another AWS account.',
        ]);

        $this->assertCount(1, $deployment->solutions());
        $this->assertStringContainsString('globally unique', $deployment->solutions()[0]);
    }

    public function test_domain_name_already_exists()
    {
        $deployment = new Deployment([
            'status_message' => 'The domain name you provided already exists.',
        ]);

        $this->assertCount(1, $deployment->solutions());
        $this->assertStringContainsString('Ensure the domain', $deployment->solutions()[0]);
    }

    public function test_function_exceeds_maximum_allowed_size()
    {
        $deployment = new Deployment([
            'status_message' => 'Function code combined with layers exceeds the maximum allowed size',
        ]);

        $this->assertCount(1, $deployment->solutions());
        $this->assertStringContainsString('to a Docker runtime', $deployment->solutions()[0]);
    }

    public function test_resource_update_in_progress()
    {
        $deployment = new Deployment([
            'status_message' => 'The operation cannot be performed at this time. An update is in progress for resource:',
        ]);

        $this->assertCount(1, $deployment->solutions());
        $this->assertStringContainsString('AWS is running updates on your infrastructure.', $deployment->solutions()[0]);
    }

    public function test_run_deployment_hooks_timed_out()
    {
        $deployment = new Deployment([
            'project_id' => 1,
            'environment' => ['name' => 'foo'],
            'status_message' => 'App\Jobs\RunDeploymentHooks has been attempted too many times or run too long. The job may have previously timed out.',
        ]);

        $this->assertCount(2, $deployment->solutions());
        $this->assertStringContainsString('https://vapor.laravel.com/app/projects/1/environments/foo/logs', $deployment->solutions()[1]);
    }
}
