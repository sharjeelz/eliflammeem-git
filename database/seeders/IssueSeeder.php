<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Issue;
use App\Models\IssueCategory;
use App\Models\RosterContact;
use App\Models\School;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IssueSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            'Transport' => [
                ['Bus delay', 'The school bus was 30 minutes late today.'],
                ['Bus safety', 'Concern about seatbelts not being used on school buses.'],
                ['Drop-off chaos', 'Morning drop-off took too long due to poor coordination.'],
                ['Late arrival', 'School bus arrives late every morning, causing my child to miss the first class.'],
                ['Parking issues', 'Parents struggle to find parking during pick-up times.'],
                ['Noisy bus', 'The bus is too noisy, making it hard for my child to focus.'],
            ],
            'Academics' => [
                ['Excellent teacher', 'My child loves the new math teacher, very engaging.'],
                ['Homework load', 'Too much homework daily, affecting child\'s free time.'],
                ['Teacher absent', 'Teacher was absent for 3 days without substitute.'],
                ['Teaching methods', 'The way subjects are taught seems outdated and unengaging.'],
                ['Classroom management', 'The class is too disruptive, and the teacher struggles to maintain control.'],
                ['Poor feedback', 'Teachers take too long to provide feedback on assignments.'],
            ],
            'Behavior' => [
                ['Bullying incident', 'A student keeps bullying my child, please take action.'],
                ['Classroom behavior', 'Class disruption is frequent; students aren\'t managed.'],
                ['Fighting in class', 'Students were fighting in the class, disrupting the lesson.'],
                ['Verbal abuse', 'A student used inappropriate language towards my child.'],
                ['Teasing', 'Other students constantly tease my child about their appearance.'],
                ['Detention unfairness', 'My child was unfairly given detention for a minor mistake.'],
            ],
            'Food & Dining' => [
                ['Cafeteria hygiene', 'Food was cold and the tables were dirty.'],
                ['Menu variety', 'Very limited healthy options in the cafeteria menu.'],
                ['Food quality', 'The food served is not fresh and tastes bland.'],
                ['Portion size', 'The portions are too small for my child to be satisfied.'],
                ['Dietary options', 'There are no vegetarian or gluten-free options in the cafeteria.'],
                ['Expired food', 'My child found expired food in the cafeteria today.'],
            ],
            'Facilities' => [
                ['AC not working', 'Classroom AC not working properly during summer heat.'],
                ['Broken chairs', 'Several chairs in class are broken and unsafe.'],
                ['Leaky roof', 'There is a leak in the roof of the classroom that needs urgent fixing.'],
                ['Bathrooms unclean', 'School bathrooms are dirty and poorly maintained.'],
                ['Lack of sports equipment', 'The school doesn\'t have enough sports equipment for the students.'],
                ['Library books', 'Many books in the library are outdated and in poor condition.'],
            ],
            'Fees & Payments' => [
                ['Fee discrepancy', 'I was charged twice for this month\'s fee payment.'],
                ['Invoice clarity', 'Please add clearer breakdowns to fee invoices.'],
                ['Late payment penalty', 'The penalty for late payment seems unreasonable.'],
                ['Payment processing issues', 'I had trouble processing my payment online and had to call support.'],
                ['Scholarship application', 'I didn\'t receive any confirmation after applying for a scholarship.'],
                ['Overcharged', 'The fees for extracurricular activities are much higher than expected.'],
            ],
            'Communication' => [
                ['Good communication', 'Appreciate how quickly staff responded to my concern.'],
                ['Hard to reach', 'Phone lines are often busy; no callback received.'],
                ['Unresponsive emails', 'Emails to the school\'s administration take too long to receive responses.'],
                ['Lack of updates', 'We didn\'t receive any updates regarding my child\'s school event.'],
                ['Parent-teacher meetings', 'The parent-teacher meetings are not scheduled at convenient times for parents.'],
                ['Poor communication on events', 'I didn\'t know about the school event until the last minute.'],
            ],
        ];

        $tenant = Tenant::first();

        if (! $tenant) {
            $this->command->error('No tenants found. Provision a tenant first via the Nova super-admin panel.');

            return;
        }

        tenancy()->initialize($tenant);

        $school = School::where('tenant_id', $tenant->id)->first();
        $branch = Branch::where('tenant_id', $tenant->id)->first();
        $rosterContact = RosterContact::first();
        $assignedUser = User::first();

        $missing = array_filter([
            'school' => $school,
            'branch' => $branch,
            'roster contact' => $rosterContact,
            'user' => $assignedUser,
        ], fn ($v) => $v === null);

        if ($missing) {
            $this->command->error('Missing required records for tenant '.$tenant->id.': '.implode(', ', array_keys($missing)));
            tenancy()->end();

            return;
        }

        $seeded = 0;

        foreach ($samples as $categoryName => $issues) {
            $category = IssueCategory::where('name', $categoryName)->first();

            if (! $category) {
                $this->command->warn("Category '{$categoryName}' not found — skipping.");

                continue;
            }

            foreach ($issues as [$title, $description]) {
                Issue::create([
                    'school_id' => $school->id,
                    'branch_id' => $branch->id,
                    'public_id' => strtoupper(Str::random(8)),
                    'title' => $title,
                    'description' => $description,
                    'status' => 'new',
                    'priority' => 'medium',
                    'issue_category_id' => $category->id,
                    'roster_contact_id' => $rosterContact->id,
                    'source_role' => 'parent',
                    'assigned_user_id' => $assignedUser->id,
                ]);

                $seeded++;
            }
        }

        tenancy()->end();

        $this->command->info("Seeded {$seeded} issues for tenant: {$tenant->id}");
    }
}
