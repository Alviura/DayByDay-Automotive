<?php

namespace Database\Seeders;

use App\Models\ApprovalDemonstration;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Database\Seeder;

class ApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@daybyday.test')->first();

        if (! $admin) {
            return;
        }

        $service = app(ApprovalService::class);

        $pending = ApprovalDemonstration::updateOrCreate(
            ['reference' => 'DEMO-PENDING-001'],
            [
                'title' => 'Demo quotation series — brake pads shipment',
                'description' => 'Sample pending approval for administrator review. Represents a quotation series from the legacy approval workflow.',
                'module_type' => 'demonstration',
                'workflow_status' => 'draft',
            ]
        );

        if (! $pending->hasOpenApproval()) {
            $pending->update(['workflow_status' => 'pending']);
            $service->submit($pending, $admin, 'Please review this demonstration request.');
        }

        $approved = ApprovalDemonstration::updateOrCreate(
            ['reference' => 'DEMO-APPROVED-001'],
            [
                'title' => 'Demo stock transfer — warehouse to shop',
                'description' => 'Already approved sample for timeline/history display.',
                'module_type' => 'demonstration',
                'workflow_status' => 'draft',
            ]
        );

        if (! $approved->approval) {
            $approval = $service->submit($approved, $admin, 'Auto-seeded approved example.');
            $service->approve($approval, $admin, 'Looks good for demonstration purposes.');
        }

        $returned = ApprovalDemonstration::updateOrCreate(
            ['reference' => 'DEMO-RETURNED-001'],
            [
                'title' => 'Demo stock adjustment — count variance',
                'description' => 'Returned to requester for additional documentation.',
                'module_type' => 'demonstration',
                'workflow_status' => 'draft',
            ]
        );

        if (! $returned->approval) {
            $approval = $service->submit($returned, $admin, 'Needs supporting count sheet.');
            $service->returnForRevision($approval, $admin, 'Please attach the signed stock count sheet before approval.');
        }
    }
}
