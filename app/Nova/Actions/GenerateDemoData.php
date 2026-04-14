<?php

namespace App\Nova\Actions;

use App\Models\AccessCode;
use App\Models\Branch;
use App\Models\Issue;
use App\Models\IssueCategory;
use App\Models\RosterContact;
use App\Models\School;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\Permission\PermissionRegistrar;

class GenerateDemoData extends Action
{
    use Queueable;

    public function name(): string
    {
        return 'Generate Demo Data';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make(
                '<p class="text-blue-600 font-bold text-base">This will add dummy data for testing (existing data is not affected):</p>'
                .'<ul class="mt-2 text-sm text-gray-700 list-disc list-inside space-y-1">'
                .'<li>1 branch manager + 2 staff per branch (assigned to 1–2 categories)</li>'
                .'<li>3 parents + 3 teachers per branch, each with an access code</li>'
                .'<li>1 open issue per contact, titles matched to the contact\'s category</li>'
                .'</ul>'
                .'<p class="mt-3 text-amber-600 text-sm font-semibold">&#9888; If the school status is <strong>inactive</strong>, generated contacts and issues will be created but the public portal will remain suspended — set status to <strong>active</strong> in Nova before testing the portal.</p>'
            )->asHtml(),
        ];
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $tenant) {
            try {
                tenancy()->initialize($tenant);

                DB::transaction(fn () => $this->seed($tenant));

                tenancy()->end();
            } catch (\Throwable $e) {
                tenancy()->end();
                report($e);

                return Action::danger('Demo data generation failed: '.$e->getMessage());
            }
        }

        return Action::message('Demo data generated. Staff, parents, teachers, and category-matched issues created for each branch.');
    }

    // ── Core seeder ──────────────────────────────────────────────────────────

    private function seed(object $tenant): void
    {
        $tenantId = $tenant->id;

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $school = School::where('tenant_id', $tenantId)->first();

        if (! $school) {
            throw new \RuntimeException('No school found. Please provision the tenant first.');
        }

        $branches = Branch::where('tenant_id', $tenantId)
            ->where('school_id', $school->id)
            ->get();

        if ($branches->isEmpty()) {
            throw new \RuntimeException('No branches found. Please add at least one branch before generating demo data.');
        }

        // Only use the standard categories seeded by TenantIssueCategoriesSeeder
        $categories = IssueCategory::where('tenant_id', $tenantId)
            ->whereIn('name', [
                'Transport', 'Academics', 'Facilities', 'Behavior',
                'Food & Dining', 'Communication', 'Health & Safety',
                'Fees & Payments', 'Technology Issues', 'General Complaints',
            ])
            ->get();

        foreach ($branches as $branch) {
            $staffMembers = $this->seedStaff($branch, $tenantId, $categories);
            $contacts     = $this->seedContacts($branch, $school->id, $tenantId);
            $this->seedIssues($contacts, $branch, $school->id, $tenantId, $categories, $staffMembers);
        }
    }

    // ── Staff & managers ─────────────────────────────────────────────────────

    private function seedStaff(Branch $branch, string $tenantId, \Illuminate\Database\Eloquent\Collection $categories): array
    {
        $slug = Str::slug($branch->name);

        $this->createUser(
            name: $this->randomStaffName(),
            email: "demo.mgr.{$slug}.".Str::lower(Str::random(4)).'@demo.test',
            tenantId: $tenantId,
            role: 'branch_manager',
            branch: $branch,
            pivotTitle: 'Branch Manager',
            categories: collect(),
        );

        $staff = [];
        for ($i = 0; $i < 2; $i++) {
            $assignedCats = $categories->isNotEmpty()
                ? $categories->random(min(rand(1, 2), $categories->count()))
                : collect();

            $staff[] = $this->createUser(
                name: $this->randomStaffName(),
                email: "demo.staff.{$slug}.".Str::lower(Str::random(4)).'@demo.test',
                tenantId: $tenantId,
                role: 'staff',
                branch: $branch,
                pivotTitle: 'Staff',
                categories: collect($assignedCats),
            );
        }

        return $staff;
    }

    private function createUser(
        string $name,
        string $email,
        string $tenantId,
        string $role,
        Branch $branch,
        string $pivotTitle,
        \Illuminate\Support\Collection $categories,
    ): User {
        $user = User::create([
            'name'      => $name,
            'email'     => $email,
            'tenant_id' => $tenantId,
            'password'  => Hash::make('Demo@1234'),
        ]);

        $user->assignRole($role);

        $user->branches()->attach($branch->id, [
            'tenant_id' => $tenantId,
            'title'     => $pivotTitle,
        ]);

        if ($categories->isNotEmpty()) {
            $sync = $categories->mapWithKeys(fn ($c) => [$c->id => ['tenant_id' => $tenantId]])->all();
            $user->categories()->sync($sync);
        }

        return $user;
    }

    // ── Contacts & access codes ──────────────────────────────────────────────

    private function seedContacts(Branch $branch, int $schoolId, string $tenantId): array
    {
        $contacts = [];

        // 25 parents + 25 teachers = 50 contacts per branch
        $roles = array_merge(array_fill(0, 25, 'parent'), array_fill(0, 25, 'teacher'));

        foreach ($roles as $role) {
            $contact = RosterContact::create([
                'tenant_id' => $tenantId,
                'school_id' => $schoolId,
                'branch_id' => $branch->id,
                'role'      => $role,
                'name'      => $role === 'teacher' ? $this->randomTeacherName() : $this->randomParentName(),
                'email'     => "demo.{$role}.".Str::lower(Str::random(7)).'@demo.test',
                'phone'     => '+1555'.rand(1000000, 9999999),
            ]);

            AccessCode::create([
                'tenant_id'         => $tenantId,
                'roster_contact_id' => $contact->id,
                'branch_id'         => $branch->id,
                'code'              => strtoupper(Str::random(6)),
                'channel'           => 'manual',
                'expires_at'        => now()->addDays(90),
            ]);

            $contacts[] = $contact;
        }

        return $contacts;
    }

    // ── Issues ───────────────────────────────────────────────────────────────

    private function seedIssues(
        array $contacts,
        Branch $branch,
        int $schoolId,
        string $tenantId,
        \Illuminate\Database\Eloquent\Collection $categories,
        array $staffMembers,
    ): void {
        $priorities = ['low', 'medium', 'high', 'urgent'];
        // Weight statuses: mostly new/in_progress (open), a few resolved — never closed
        // so the contact's access code stays usable and all issues show in default list view
        $statuses = ['new', 'new', 'new', 'new', 'in_progress', 'in_progress', 'in_progress', 'resolved'];

        foreach ($contacts as $contact) {
            // One issue per contact — contacts can only have one open issue at a time
            $category = $categories->isNotEmpty() ? $categories->random() : null;
            $status   = $statuses[array_rand($statuses)];
            $ctx      = [
                'student' => $this->randomStudentName(),
                'class'   => $this->randomClass(),
                'section' => $this->randomSection(),
            ];

            do {
                $publicId = strtoupper(Str::random(8));
            } while (Issue::where('tenant_id', $tenantId)->where('public_id', $publicId)->exists());

            Issue::create([
                'tenant_id'         => $tenantId,
                'school_id'         => $schoolId,
                'branch_id'         => $branch->id,
                'roster_contact_id' => $contact->id,
                'public_id'         => $publicId,
                'title'             => $this->titleForCategory($category, $contact->role),
                'description'       => $this->descriptionForCategory($category, $contact->role, $ctx),
                'priority'          => $priorities[array_rand($priorities)],
                'status'            => $status,
                'source_role'       => $contact->role,
                'issue_category_id' => $category?->id,
            ]);
        }
    }

    // ── Category → title matching ────────────────────────────────────────────

    /**
     * Pick a realistic issue title matched to the category name and the
     * contact's role (parent vs teacher).  Falls back to a general pool when
     * no keyword matches.
     */
    private function titleForCategory(?IssueCategory $category, string $role): string
    {
        if (! $category) {
            $pool = $role === 'teacher'
                ? $this->teacherTitles('general')
                : $this->parentTitles('general');

            return $pool[array_rand($pool)];
        }

        $name = strtolower($category->name ?? '');

        $bucket = match (true) {
            $this->matches($name, ['general'])
                => 'general',
            $this->matches($name, ['bully', 'behav', 'conduct', 'harass', 'violence', 'threat'])
                => 'bullying',
            $this->matches($name, ['academ', 'learn', 'study', 'homework', 'curriculum', 'exam', 'grade', 'tutor', 'class', 'subject', 'lesson'])
                => 'academic',
            $this->matches($name, ['attend', 'leave', 'absent', 'absence', 'punctual', 'late'])
                => 'attendance',
            $this->matches($name, ['fee', 'financ', 'payment', 'invoice', 'billing', 'fund', 'scholar', 'charge', 'refund'])
                => 'fees',
            $this->matches($name, ['food', 'dining', 'meal', 'cafeteria', 'canteen', 'lunch', 'kitchen'])
                => 'food',
            $this->matches($name, ['facilit', 'infrastruct', 'mainten', 'repair', 'clean', 'building', 'campus', 'equip', 'toilet', 'lab'])
                => 'facilities',
            $this->matches($name, ['transport', 'bus', 'vehicle', 'commut', 'route', 'driver'])
                => 'transport',
            $this->matches($name, ['health', 'safety', 'medical', 'wellbeing', 'welfare', 'nurse', 'counsel', 'mental', 'diet', 'allerg', 'first aid'])
                => 'health',
            $this->matches($name, ['communic', 'staff', 'complaint', 'feedback', 'meeting', 'parent', 'relation', 'contact'])
                => 'communication',
            $this->matches($name, ['it', 'tech', 'portal', 'system', 'computer', 'digital', 'online', 'device', 'internet', 'software', 'hardware'])
                => 'it',
            $this->matches($name, ['event', 'activit', 'sport', 'club', 'trip', 'excurs', 'tour', 'compet', 'festival', 'ceremon'])
                => 'events',
            default => 'general',
        };

        $pool = $role === 'teacher'
            ? $this->teacherTitles($bucket)
            : $this->parentTitles($bucket);

        return $pool[array_rand($pool)];
    }

    private function matches(string $subject, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($subject, $kw)) {
                return true;
            }
        }

        return false;
    }

    // ── Parent title pools ───────────────────────────────────────────────────

    private function parentTitles(string $bucket): array
    {
        return match ($bucket) {
            'bullying' => [
                'My child is being bullied by classmates',
                'Verbal harassment reported by my child',
                'Physical altercation — my child was hurt',
                'Cyberbullying incident involving group chat',
                'My child feels unsafe during lunch break',
                'Older students are intimidating my child',
                'Repeated name-calling that is going unreported',
                'My child is being excluded from group activities',
                'Threatening messages sent to my child via school app',
                'My child witnessed a fight and is very shaken',
                // Roman Urdu
                'Mera bacha school mein darr raha hai, koi use dhamkiyan deta hai',
                'Bade class ke bachon ne mera bacha mara, main formally report karna chahta hun',
                'WhatsApp group mein mere bacha ka mazak udaya ja raha hai classmates ne',
                'Meri beti ko lunch break mein akela chhod dete hain, ignore karte hain sab',
                'Bacha school jaane se mana karta hai, kehta hai koi use bura kehta hai',
            ],
            'academic' => [
                'Homework assignments are not being sent home',
                'Textbook missing from the school supply list',
                'Concern about the grading of a recent exam',
                'Incorrect marks entered in the report card',
                'My child is struggling with maths and needs support',
                'Requesting extra tutoring or after-school help',
                'Syllabus does not appear to be followed by the teacher',
                'Exam paper was not returned after marking',
                'Online assignment portal keeps showing access errors',
                'My child does not understand lessons in the current unit',
                'Several chapters were missed due to teacher absence',
                'No feedback was provided on the submitted project',
                'Concerned about my child\'s reading level assessment',
                'Requesting differentiated learning support for my child',
                'Child has been diagnosed with dyslexia — needs accommodation',
                'Exam timetable conflicts with a religious holiday',
                'Worried about the upcoming exam schedule changes',
                'Child says the teacher moves too fast through material',
                'Report card grades do not match what was communicated',
                'Request to review the grading rubric used for the essay',
                // Roman Urdu
                'Mera bacha math mein kaafi kamzor hai, koi extra help chahiye',
                'Teacher bohot tezi se syllabus parha rahi hain, bachon ko samajh nahi aata',
                'Is hafte koi homework nahi bheja gaya ghar',
                'Result card mein marks galat likhe hain, please check karein',
                'Mera bacha class mein akela feel karta hai aur padhai mein peeche reh raha hai',
                'Exam ke baad paper wapas nahi diya gaya, marks bhi explain nahi kiye',
                'Ustaad ne pura chapter chhor diya, exam mein aayega kya?',
                'Mujhe nahi pata mera bacha kahan stand karta hai class mein, report card unclear hai',
            ],
            'attendance' => [
                'Requesting approved leave for a family event abroad',
                'My child was absent for a medical appointment — need to update records',
                'Leave application submitted two weeks ago but not yet approved',
                'Attendance record incorrectly shows three unexplained absences',
                'Requesting extended leave for an overseas family emergency',
                'My child is recovering from surgery — requesting a home-study plan',
                'Excuse letter submitted but still marked as unauthorised absence',
                'Request to clarify the school\'s leave policy for religious observance',
                'Attendance percentage dropped due to a school trip not being counted',
                'My child was marked absent on a day they were definitely in school',
            ],
            'fees' => [
                'Fee payment receipt was not issued after online transfer',
                'We were double-charged on this month\'s invoice',
                'Scholarship application submitted but not yet processed',
                'Requesting a fee instalment plan for the current term',
                'Library book fine appears to be incorrect',
                'Wrong fee category has been applied to our account',
                'Transport fee is being charged despite not using the bus',
                'Refund for cancelled field trip has not been received',
                'Annual fund receipt has not been issued',
                'Bank transfer reflected in our records but not in school system',
                'Activity fee charged for a club my child never joined',
                'Invoice sent was for a higher amount than the quoted fee',
                // Roman Urdu
                'Online payment ho gayi lekin receipt nahi mili abhi tak',
                'Is mahine double fees kaat li gayi hain, please check karein',
                'Scholarship ke liye form jama kiya tha, koi jawab nahi aaya',
                'Bus fee liya ja raha hai jabke mera bacha bus use nahi karta',
                'Trip cancel ho gayi thi, refund abhi tak nahi mila',
                'Mujhe kiraya mein thodi reaayt chahiye, please installment plan banayen',
            ],
            'food' => [
                'Cafeteria food quality has declined noticeably this term',
                'My child found the food cold and unappetising today',
                'There are no healthy options available in the cafeteria menu',
                'My child has a dietary restriction that is not being accommodated',
                'The portion sizes served at lunch are too small for my child',
                'Cafeteria runs out of food before all students are served',
                'My child found expired food in the cafeteria today',
                'The dining area tables are dirty and unhygienic',
                'No vegetarian or gluten-free option was available at lunch',
                'My child was charged for a meal they did not receive',
                // Roman Urdu
                'Canteen ka khaana bohot ganda tha, mera bacha aakar beemar hua',
                'Bachon ko thanda aur basi khaana diya ja raha hai canteen mein',
                'Mera bacha specific diet pe hai, canteen mein uske liye koi option nahi',
                'Canteen mein sab khaana khatam ho jata hai, mere bacha ko nahi milta',
                'Canteen ki tables gandy hain, bachon ki sehat ka khayal rakhen',
                'Khaane mein kuch ajeeb cheez mili, bohot ghabrahat hui',
            ],
            'facilities' => [
                'Classroom air conditioning has not been working for two weeks',
                'Drinking water dispenser on level 2 is out of order',
                'Restroom hygiene issue in the main building',
                'Playground equipment appears broken and unsafe for students',
                'Science lab equipment is outdated and not maintained',
                'Classroom projector not working — affecting lessons',
                'Lighting in the corridor near room 12 is very poor',
                'Lockers have not been assigned to students yet this term',
                'Wheelchair ramp near the main entrance is blocked',
                'Student benches in the courtyard are broken',
                // Roman Urdu
                'Class ka AC teen hafton se kharab hai, garmi mein padhai nahi hoti',
                'Peene ka paani nahi milta, cooler kharab pada hai',
                'Bathrooms bohot gandy hain, bachon ko jaane mein takleef hoti hai',
                'Playground mein jhula toot gaya hai, bachon ko chot lag sakti hai',
                'Class ki khirkiyan nahi khulti, hawa nahi aati andar',
                'Bijli ki koi problem hai corridor mein, andhera rehta hai',
            ],
            'transport' => [
                'Bus consistently arriving 20 minutes late every morning',
                'Bus route was changed without prior notification to parents',
                'Complaint about the bus driver\'s manner toward students',
                'My child was dropped at the wrong stop last week',
                'Bus is overcrowded and students are standing for the journey',
                'No air conditioning on the school bus during summer',
                'Bus did not show up on Tuesday and no message was sent',
                'Seatbelts on the bus are not functioning properly',
                'Child missed the bus because departure time was changed',
                'Bus attendant was not present on the morning route',
                // Roman Urdu
                'School bus roz late aati hai, bacha der se school pahunchta hai',
                'Bus driver mera bacha galat stop pe chhod gaya kal',
                'Bus mein bachon ke liye baithne ki jagah nahi hoti, sab khare rehte hain',
                'Aaj bus hi nahi aayi aur school ki taraf se koi message bhi nahi aaya',
                'Bus ka route badal diya gaya, humein pehle koi information nahi di gayi',
                'Garmi mein bus mein AC nahi chalta, bachon ko bohot takleef hoti hai',
            ],
            'health' => [
                'My child has been experiencing anxiety at school',
                'Allergy concern — peanut-based food is being served at lunch',
                'First aid incident occurred and no notification was sent to us',
                'My child was sent home unwell without anyone calling me first',
                'Requesting a special dietary accommodation for my child',
                'Counselling service referral needed for my child',
                'Requesting mental health support resources for our family',
                'Child reports frequent headaches — may be related to seating position',
                'Noise levels in the classroom are affecting my child\'s concentration',
                'My child has asthma and the dusty classroom is triggering symptoms',
                'Requesting allergy action plan to be reviewed with the nurse',
                'Child was given medication by school without our consent',
                // Roman Urdu
                'Mera bacha school ki wajah se tension mein hai, raat ko so nahi pata',
                'Bacha school mein gir gaya, chot lagi lekin humein koi khabar nahi di gayi',
                'Khaane mein kuch tha, mera bacha allergy ki wajah se beemar ho gaya',
                'Meri beti ko bohot zyada pressure mehsoos hota hai exams ki wajah se',
                'Bacha school se beemar wapis aaya lekin humein phone nahi kiya gaya',
                'Mujhe koi counsellor se milwana hai apne bacha ke liye, bohot stressed hai',
            ],
            'communication' => [
                'Class teacher has not responded to my messages in over a week',
                'Requesting a meeting with the class teacher',
                'Counsellor appointment was requested but never confirmed',
                'Staff member spoke disrespectfully to my child',
                'Concerned about the conduct of the substitute teacher last week',
                'Meeting request with the principal was not acknowledged',
                'Class group chat shared content that was not appropriate',
                'No communication was received about an upcoming school event',
                'Emergency contact update was submitted but not reflected in records',
                'Monthly parent newsletter has not been sent for two months',
                'I was not notified about a change to the school pick-up procedure',
                'Staff member gave incorrect information about school policy',
                // Roman Urdu
                'Ustaad ne mere message ka koi jawab nahi diya pichle ek hafte se',
                'School ki taraf se koi notice nahi aaya event ke baare mein',
                'Principal se milna chahta hun lekin appointment nahi mil rahi',
                'Ustaad ne meri bachi ke saath be-adabi se baat ki, bohot bura laga',
                'School ne pickup time badla lekin humein pehle bataya nahi',
                'Class group mein aisa content share kiya gaya jo bachon ke liye theek nahi',
                'Do mahine se koi newsletter nahi mili, pata nahi school mein kya ho raha hai',
            ],
            'it' => [
                'Student portal login stopped working after the update',
                'Online homework submission keeps failing before the deadline',
                'Parent app is not sending any notifications',
                'Video call link for the parent-teacher meeting was broken',
                'E-library access was revoked without any explanation',
                'Grades have not been updated on the student portal this term',
                'Student cannot log in to the exam platform from home',
                'Digital homework was submitted but shows as missing in the system',
                'Parent account on the school app is locked after one login attempt',
                'Assignment feedback is not visible in the student\'s portal',
                // Roman Urdu
                'Portal pe login nahi ho raha, password change kiya phir bhi error aa raha hai',
                'Online assignment submit nahi ho raha, kal deadline hai',
                'Parent app pe notifications aana band ho gayi hain',
                'Grades update nahi hue portal pe is poore term se',
                'Meeting ka video link kaam nahi kar raha tha, meeting miss ho gayi',
                'Mera account lock ho gaya ek baar galat password dalne ke baad',
            ],
            'events' => [
                'After-school activity registration query — system not accepting entry',
                'Field trip permission slip deadline was never communicated to us',
                'Sports team selection process lacks transparency',
                'Annual day performance role was allocated without an audition',
                'Science fair project submission rules were unclear',
                'Club membership fee was not refunded after we cancelled',
                'My child was not told about a change to sports practice timing',
                'Dress code for the cultural event was not communicated to parents',
                'Prize giving ceremony invitation was not received',
                'Prize giving ceremony invitation was not received',
                'School trip deposit paid but no confirmation issued',
                'After-school club is being run without qualified supervision',
            ],
            default => [
                'General query regarding school administration',
                'Request for clarification on school policy',
                'Follow-up on a previously submitted concern',
                'Requesting an update on a pending application',
                'Complaint regarding recent communication from the school',
                'Concern raised by my child that needs formal documentation',
                'Request for a meeting with school management',
                'Inquiry about student records and documentation',
                'Request to update personal information in school system',
                'Feedback on overall school experience this term',
                // Roman Urdu
                'School ki fees policy ke baare mein samajhna chahta hun',
                'Mujhe school ki ek purani shikayat ka jawab chahiye',
                'Mera bacha school mein khush nahi, main discuss karna chahta hun',
                'Kisi baat ka jawab nahi mila, isliye yahan submit kar raha hun',
                'Principal se mulaqat ka waqt chahiye',
                'School mein kuch aisa hua jo mujhe theek nahi laga, document karna chahta hun',
                'Please mujhe bataen yeh masla kahan report karna chahiye',
                'Mujhe apne bacha ki records ki copy chahiye',
            ],
        };
    }

    // ── Teacher title pools ──────────────────────────────────────────────────

    private function teacherTitles(string $bucket): array
    {
        return match ($bucket) {
            'bullying' => [
                'Bullying incident observed between two students in my class',
                'Student repeatedly disrupting class with threatening language',
                'Physical altercation occurred in my classroom during free period',
                'Cyberbullying complaint raised by a student in my class',
                'Student is being socially isolated by peers — requesting support',
                'A student has disclosed being bullied to me — escalating formally',
                'Repeated intimidation behaviour from one student toward others',
                'Student exhibiting aggressive behaviour — needs intervention',
                'Verbal abuse overheard during a class transition — needs follow-up',
                'Student confided about being threatened outside of school grounds',
                // Roman Urdu
                'Do students mein larai ho gayi class mein, formally report kar raha hun',
                'Ek student ne doosre ko dhamki di, parents ko bhi khabar karna hogi',
                'Student ne mujhe bataya ke usse school ke bahar bhi daraya ja raha hai',
                'Class mein ek student baaki sab ko intimidate karta hai, madad chahiye',
            ],
            'academic' => [
                'Requesting additional teaching resources for the current unit',
                'Curriculum materials were not delivered on time for this term',
                'Textbooks ordered for my class have still not arrived',
                'Requesting approval to modify lesson plan due to student needs',
                'Two students in my class require urgent learning support referrals',
                'Marking scheme for the upcoming exam has not been shared with staff',
                'Request to schedule a student progress review for three students',
                'No guided reading materials available for lower ability group',
                'Requesting IEP support for a student with learning difficulties',
                'Lesson planning time has been cut — impacting preparation quality',
                'Academic progress report template has errors — needs correction',
                'Assessment rubric was changed without notifying teaching staff',
                'Requesting training on the new differentiation framework',
                'Student in my class is significantly below grade level — next steps?',
                'Requesting permission to set an alternative assessment format',
                'Exam invigilation schedule conflicts with my class timetable',
            ],
            'attendance' => [
                'Student has been absent for five consecutive days — no contact received',
                'Multiple students from one class are consistently late on Mondays',
                'Attendance register system is not saving entries correctly',
                'Student is frequently leaving early without proper sign-out',
                'Requesting clarification on the process for marking authorised absence',
                'Parent claims attendance marked incorrectly — requires review',
                'Student absent during exams — requesting guidance on missed paper policy',
                'Class register was not updated by the substitute — needs correction',
                'Three students have exceeded absence threshold — escalation needed',
                'Student arrives late daily and disrupts the class — requesting support',
            ],
            'fees' => [
                'Student unable to participate in field trip due to unpaid fees',
                'Classroom supply budget not released for this term',
                'Requesting reimbursement for classroom materials purchased personally',
                'Student lab fees were collected but equipment still not ordered',
                'Requesting clarity on the process for applying for fee waivers for needy students',
                'Extracurricular activity budget allocation not yet communicated to teachers',
                'Requesting petty cash approval for urgent classroom supplies',
                'Arts and crafts budget for the term has been exhausted already',
            ],
            'food' => [
                'Requesting a review of the cafeteria menu for healthier options',
                'A student in my class had an allergic reaction to cafeteria food',
                'Cafeteria food consistently runs out before all students are served',
                'Students are complaining about poor food quality this term',
                'Requesting accommodation for a student with dietary restrictions',
                'Dining area is unhygienic — tables not cleaned between sittings',
                'Cafeteria portion sizes are insufficient for growing students',
                'A student found a foreign object in their meal today',
            ],
            'facilities' => [
                'Classroom projector has been broken for three weeks — affecting lessons',
                'Whiteboard markers have run out and replacement request is pending',
                'Classroom heating is not working and the room is very cold',
                'Science lab chemicals are expired and need to be replaced',
                'Request to repair the broken cabinet in room 7A',
                'Air conditioning unit in my classroom is making loud noise',
                'Classroom chairs are broken — students cannot sit comfortably',
                'Requesting a second whiteboard for the classroom',
                'Storage room allocated to my department is inaccessible — lock issue',
                'Shared printer on the teacher floor keeps jamming',
                'Staff restroom on the second floor is out of order',
                'Classroom window cannot be closed — noise and weather issue',
                'Fire extinguisher in my classroom has not been inspected this year',
            ],
            'transport' => [
                'Field trip transport has not been confirmed despite approval',
                'Requesting a dedicated bus for the upcoming sports day',
                'Bus schedule for the school excursion has not been shared with staff',
                'Students were late to external exam due to delayed transport',
                'Requesting confirmation of bus allocation for tomorrow\'s trip',
                'Transport logistics for the annual trip are incomplete',
            ],
            'health' => [
                'Student fainted in my classroom — requesting a follow-up from the nurse',
                'A student had an allergic reaction during class — incident to be logged',
                'Student disclosed self-harm concerns to me — escalating to counsellor',
                'Requesting first aid refresher training for classroom teachers',
                'Student\'s medication is kept in my classroom — need proper storage guidance',
                'Several students showing symptoms of illness — requesting health check',
                'Student with epilepsy had a minor episode — action plan needs review',
                'Requesting updated emergency medical information for my class list',
                'Student disclosed significant mental health struggles — counsellor needed',
                'Classroom humidity is causing respiratory discomfort for students',
            ],
            'communication' => [
                'Requesting a meeting with the branch manager regarding timetable issues',
                'Staff schedule change was not communicated with adequate notice',
                'Department meeting was rescheduled without notifying all staff',
                'Parent escalated directly to administration without speaking to me first',
                'Requesting feedback on my recent performance observation',
                'Substitute teacher cover for my maternity leave has not been arranged',
                'No response received to my leave request submitted two weeks ago',
                'Annual leave policy for teachers needs to be clarified in writing',
                'I did not receive the updated staff handbook for this term',
                'Parent is contacting me via personal number — requesting school channel protocol',
                'Staff notice board is outdated — key announcements are being missed',
                'Requesting minutes from last month\'s department meeting',
            ],
            'it' => [
                'Smartboard in room 4B is not functioning — urgent before exams',
                'Staff portal login is not working since the system update',
                'Grade entry system keeps logging me out mid-submission',
                'Online assessment platform timed out during a live test',
                'Requesting IT setup for the new classroom laptop',
                'Student devices not connecting to the school Wi-Fi in block C',
                'Virtual classroom link was broken during a remote lesson',
                'Requesting access to the digital resources library for my subject',
                'Report generation tool in the teacher portal is not working',
                'Classroom response system (clickers) are all offline',
                'Requesting training on the new learning management system',
                'USB ports on classroom computers have been disabled — need access',
            ],
            'events' => [
                'Sports day logistics have not been communicated to PE staff',
                'Requesting supplies for the upcoming science fair',
                'Annual day rehearsal schedule conflicts with exam preparation',
                'Field trip has been approved but transport not yet arranged',
                'Requesting a dedicated practice slot for the drama performance',
                'Permission forms for the upcoming trip have not been prepared yet',
                'Sponsorship for the debate competition has not been confirmed',
                'Requesting approval to take students off-campus for a project visit',
                'Cultural day activity plan submitted but no acknowledgement received',
                'Awards ceremony preparation requires department heads to coordinate',
            ],
            default => [
                'Requesting guidance on a new administrative process',
                'Follow-up needed on a student welfare referral',
                'Requesting approval for a classroom initiative',
                'Query about a recent policy change that affects my teaching',
                'Requesting support resources for classroom management',
                'Escalating a student concern that has not been resolved informally',
                'Feedback on the new reporting format — causing confusion',
                'Requesting updated student records for my class',
                'Inquiry about professional development opportunities this term',
                'Requesting a timetable adjustment to avoid a scheduling conflict',
                // Roman Urdu
                'Ek student ke baare mein guidance chahiye, kuch ajeeb behavior show kar raha hai',
                'Main ne yeh masla verbally bataya tha, ab formally submit kar raha hun',
                'Mujhe nahi pata yeh kahan report karna chahiye, please route karein',
                'Yeh pehli baar nahi ho raha, ek permanent solution chahiye',
                'Class mein kuch aisa hua jo record mein aana chahiye',
            ],
        };
    }

    // ── Category + role matched descriptions ─────────────────────────────────

    private function descriptionForCategory(?IssueCategory $category, string $role, array $ctx = []): string
    {
        if (! $category) {
            return $this->descriptionForRole($role);
        }

        $name   = strtolower($category->name ?? '');
        $bucket = match (true) {
            $this->matches($name, ['bully', 'behav', 'conduct', 'harass', 'violence', 'threat']) => 'bullying',
            $this->matches($name, ['academ', 'learn', 'study', 'homework', 'curriculum', 'exam', 'grade', 'tutor', 'class', 'subject', 'lesson']) => 'academic',
            $this->matches($name, ['attend', 'leave', 'absent', 'absence', 'punctual', 'late']) => 'attendance',
            $this->matches($name, ['fee', 'financ', 'payment', 'invoice', 'billing', 'fund', 'scholar', 'charge', 'refund']) => 'fees',
            $this->matches($name, ['food', 'dining', 'meal', 'cafeteria', 'canteen', 'lunch', 'kitchen']) => 'food',
            $this->matches($name, ['facilit', 'infrastruct', 'mainten', 'repair', 'clean', 'building', 'campus', 'equip', 'toilet', 'lab']) => 'facilities',
            $this->matches($name, ['transport', 'bus', 'vehicle', 'commut', 'route', 'driver']) => 'transport',
            $this->matches($name, ['health', 'safety', 'medical', 'wellbeing', 'welfare', 'nurse', 'counsel', 'mental', 'diet', 'allerg', 'first aid']) => 'health',
            $this->matches($name, ['communic', 'staff', 'complaint', 'feedback', 'meeting', 'parent', 'relation', 'contact']) => 'communication',
            $this->matches($name, ['it', 'tech', 'portal', 'system', 'computer', 'digital', 'online', 'device', 'internet', 'software', 'hardware']) => 'it',
            $this->matches($name, ['event', 'activit', 'sport', 'club', 'trip', 'excurs', 'tour', 'compet', 'festival', 'ceremon']) => 'events',
            default => 'general',
        };

        $pools = [
            'bullying' => [
                'parent' => [
                    'My child {student} is in {class}, Section {section} and has been coming home visibly upset for the past two weeks. They told me a group of classmates has been calling them names and excluding them from group activities. I have spoken to {student} at length but they are now reluctant to attend school. I am formally submitting this so the school is aware and takes immediate action.',
                    'I am writing to report ongoing bullying involving my child {student} ({class}, Section {section}). An older student has been intimidating {student} near the lockers every morning before class. {student} did not want me to escalate but I feel I have no choice — this has been happening for over three weeks now.',
                    '{student} came home in tears yesterday and finally told me what has been happening in {class}, Section {section}. A classmate has been sending threatening messages via the class group chat and making offensive comments during lessons. I have screenshots and would like to formally report this and request an urgent meeting.',
                    'My child {student} in {class} Section {section} has been physically pushed by a classmate on at least two occasions this term. {student} spoke to the form teacher but says nothing was done. I want this formally documented and I expect to be updated on what action has been taken.',
                    'I am very concerned about the wellbeing of {student}, who is enrolled in {class}, Section {section}. {student} has been socially excluded by their peer group for the past month and is showing signs of anxiety and low self-confidence. Please let me know who I should speak to and what the school\'s next steps are.',
                    '{student} told me that a group in {class}, Section {section} has been mocking them for their appearance and accent. This has been going on since the start of term. I believe it qualifies as targeted harassment and I want the school to treat it with the seriousness it deserves.',
                    'My child {student} ({class}, Section {section}) was pushed in the corridor last week and the incident was witnessed by other students. I was not informed by the school — I only found out when {student} came home with a bruise. I expect a formal explanation and a follow-up.',
                    'I want to formally report that {student} in {class}, Section {section} is being cyberbullied through the class WhatsApp group. The messages are degrading and have caused significant distress. I am available for a meeting at any time this week.',
                ],
                'teacher' => [
                    'I am formally reporting a bullying incident observed in {class}, Section {section} involving {student}. I intervened immediately when I witnessed the confrontation during the morning session. I have documented what I saw and the names of the students involved, and I request that management review this and inform the parents.',
                    '{student} in {class} Section {section} disclosed to me privately that they are being regularly bullied by a group of classmates. I have followed the initial safeguarding step and am now escalating for formal action. Immediate intervention is necessary to prevent this from escalating further.',
                    'A serious altercation occurred in {class}, Section {section} today involving {student} and two other students. I separated them and took witness statements. This is the third incident this term involving the same students and a structured intervention is now essential.',
                    'I am concerned about the social dynamics within {class}, Section {section}. {student} is being deliberately excluded and I have observed whispered comments and group laughter directed at them during lessons. This is affecting their participation and confidence in class.',
                    '{student} in {class}, Section {section} showed me bruises and explained they were caused by a classmate during the lunch break. I have filed a first aid note separately. This needs immediate investigation by the branch manager and both families should be contacted today.',
                    'I have observed a sustained pattern of intimidation toward {student} across multiple lessons in {class}, Section {section}. The behaviour includes verbal taunts and deliberate interference with {student}\'s work. I have spoken informally to the instigating student; formal action is now required.',
                    'A student reported to me that {student} in {class}, Section {section} has been threatened and told not to speak to certain peers. I have taken a written note and am submitting this formally. I request a safeguarding review and a parent meeting within the week.',
                ],
            ],
            'academic' => [
                'parent' => [
                    'My child {student} is enrolled in {class}, Section {section} and has been struggling with Mathematics throughout this term. Despite asking the teacher for help, {student} reports the class moves too quickly for them to keep up. I would like to request additional support or after-school tutoring arrangements.',
                    'I am concerned about the academic progress of {student} in {class}, Section {section}. The recent exam results do not reflect the effort {student} has been putting in at home. I would like to understand how the marks were calculated and whether a review is possible.',
                    'Homework has not been sent home consistently for {student} in {class}, Section {section} over the past three weeks. I have checked with other parents in the same section and they share this concern. {student} is falling behind and I am worried about the upcoming assessments.',
                    '{student} in {class}, Section {section} received a report card with marks that do not match what was communicated verbally during the parent-teacher meeting. The discrepancy in English is particularly significant. I would like this reviewed and corrected before the end of term.',
                    'My child {student} is in {class}, Section {section} and was recently diagnosed with dyslexia. I have shared the assessment report with the school already. I am requesting that appropriate accommodations be put in place urgently for the upcoming examinations.',
                    'I submitted a request for academic support for {student} ({class}, Section {section}) six weeks ago and have received no response. {student} continues to struggle and I feel this has not been prioritised. Please advise on what options are available.',
                    '{student} in {class}, Section {section} says several chapters have been skipped this term due to teacher absences and no substitute lesson plan was followed. I am concerned this will affect {student}\'s exam preparation significantly.',
                    'The exam paper for the mid-term assessment was never returned to {student} ({class}, Section {section}) despite other students receiving theirs three weeks ago. {student} needs feedback to understand their mistakes before the final examination.',
                ],
                'teacher' => [
                    'I am requesting formal learning support for {student} in {class}, Section {section}. Over the past six weeks I have documented a consistent pattern of difficulty with reading comprehension and written expression. The data supports the need for an IEP review at the earliest opportunity.',
                    '{student} in {class} Section {section} is significantly below the expected grade level for this point in the academic year. I have tried differentiated instruction within the classroom but the gap has not narrowed. I am requesting a referral to the learning support team.',
                    'The curriculum materials for {class}, Section {section} have still not been delivered. I have been using photocopies of last year\'s materials for three weeks. {student} and others in the group are missing key resources for the current unit.',
                    'I would like to schedule a formal academic review for {student} ({class}, Section {section}). Their attainment has dropped noticeably since mid-term and I am concerned there may be external factors. A meeting with parents and the branch manager is recommended.',
                    'The marking scheme for the most recent assessment was changed after papers were submitted for {class}, Section {section}. This has resulted in inconsistent grading. I would like a standardisation meeting arranged and marks reviewed before they are communicated to parents.',
                    '{student} in {class}, Section {section} submits all assignments on time and participates actively in class. However, their exam scores are consistently lower than expected. I believe there may be assessment anxiety at play and I am requesting a counsellor referral.',
                    'The assessment rubric for the recent essay task was not shared with staff before marking began. I assessed {student}\'s work ({class}, Section {section}) using the previous format, which may have disadvantaged them. I am requesting a retrospective review.',
                    'Lesson planning time for {class}, Section {section} has been cut by the new timetable. I no longer have adequate preparation time for {student}\'s group, and the quality of differentiated support I can provide has declined as a direct result.',
                ],
            ],
            'attendance' => [
                'parent' => [
                    'I am submitting a formal leave request for my child {student} in {class}, Section {section}. We have a family medical appointment abroad scheduled for next week requiring five days of approved absence. I submitted a written application to the office two weeks ago but have not received any confirmation.',
                    '{student} in {class}, Section {section} was marked absent last Thursday when they were actually present in school. I have the morning drop-off record and a witness who can confirm this. I would like the attendance register corrected as the current record may affect {student}\'s percentage.',
                    'My child {student} ({class}, Section {section}) has been unwell and the doctor has advised at least two weeks of rest following minor surgery. I have the medical certificate ready to submit. I am requesting an approved absence and a home-study plan so {student} does not fall too far behind.',
                    'I submitted an excuse letter on behalf of {student} ({class}, Section {section}) for the absence on the 5th and 6th due to a religious observance. The absences are still showing as unauthorised in the system. Please update the records as this was a valid and pre-notified absence.',
                    'The attendance record for {student} in {class}, Section {section} shows three unexplained absences that I cannot account for. I was not contacted by the school on any of those days. I would like clarification on the exact dates before I can respond further.',
                    '{student} in {class} Section {section} has been recovering from a viral infection and has missed eight school days this month. All absences were communicated to the class teacher by phone. I am requesting that these be marked as medically authorised and that {student} receives the missed work.',
                ],
                'teacher' => [
                    '{student} in {class}, Section {section} has been absent for six consecutive school days with no communication received from the parent or guardian. I have attempted to contact the family twice with no response. I am escalating this to the branch manager for a formal welfare check.',
                    'The attendance register for {class}, Section {section} was not completed by the substitute teacher on the day I was absent. Several students including {student} now have incorrectly recorded absences. I am requesting the system be corrected using the student sign-in sheet.',
                    '{student} in {class}, Section {section} has been arriving consistently late on Monday mornings, disrupting the start of the first lesson. I have spoken to {student} informally but the pattern continues. I am requesting the branch manager contact the family.',
                    'I am escalating a concern about {student} in {class}, Section {section} who has now exceeded the authorised absence threshold for this term. Despite repeated reminders, no explanation or documentation has been provided. Formal action is required.',
                    'A pattern has emerged where {student} in {class}, Section {section} leaves early every Wednesday without proper sign-out. I have raised this with the front desk but the issue persists. Please clarify the procedure and communicate it to all relevant parties.',
                    '{student} was absent during the mid-term examination in {class}, Section {section}. The family has since explained there was a medical emergency. I am requesting guidance on the missed paper policy so I can advise them correctly on the options available.',
                ],
            ],
            'fees' => [
                'parent' => [
                    'I made the term fee payment for {student} ({class}, Section {section}) via bank transfer on the due date. The receipt has not been issued and the parent portal still shows a balance outstanding. I have the transaction reference number and am happy to provide it immediately.',
                    'I was charged twice for {student}\'s activity fee this month. {student} is in {class}, Section {section}. I have the bank statement showing both deductions and would like an immediate refund of the duplicate charge.',
                    '{student} in {class}, Section {section} does not use the school bus but the transport fee has been applied to our account for the second consecutive month. I raised this last month and was told it would be corrected — it has not been. Please resolve this and issue a credit note.',
                    'I submitted a scholarship application for {student} ({class}, Section {section}) before the advertised deadline and have not received any acknowledgement. It has now been over a month and I would like an update on the status.',
                    'The school trip for {student}\'s class ({class}, Section {section}) was cancelled last month. The deposit I paid has still not been refunded despite the school confirming a refund would be processed within two weeks.',
                    'The fee invoice I received for {student} ({class}, Section {section}) shows a higher amount than what was communicated in the annual fee schedule. I would like a detailed breakdown of all charges before making any payment.',
                    'I have requested an instalment plan for {student}\'s fees ({class}, Section {section}) as we are going through a difficult financial period. I sent this request to the accounts office three weeks ago and have had no response.',
                ],
                'teacher' => [
                    'The classroom supply budget for {class}, Section {section} has not been released yet this term. I have been purchasing stationery and printed materials personally so that lessons can continue. I am requesting reimbursement for the receipts I have kept.',
                    '{student} in {class}, Section {section} was unable to participate in the field trip today due to an outstanding fee balance. I would like to know whether a fee waiver or deferral option is available so this student is not disadvantaged further.',
                    'Lab fees were collected from students in {class}, Section {section} at the start of term, including from {student}. The science equipment that was supposed to be ordered has still not arrived. I need it for a practical lesson scheduled next week.',
                    'The arts and crafts budget for {class}, Section {section} was exhausted by week four. We have five weeks remaining and no funds to purchase materials. I am requesting an emergency allocation to complete the planned project work.',
                    'I submitted a petty cash request for urgent supplies needed by {class}, Section {section} two weeks ago. The request was approved by the branch manager but the funds have not been transferred. Could accounts please process this as a priority?',
                ],
            ],
            'food' => [
                'parent' => [
                    'My child {student} ({class}, Section {section}) has a severe tree nut allergy that was documented at enrolment. Despite this, {student} was served food containing almonds at the cafeteria this week. This is a serious safety issue and I need written confirmation of how it will be prevented in future.',
                    '{student} in {class}, Section {section} has been coming home hungry regularly this week. They tell me the cafeteria runs out of food before their lunch slot. {student} is a growing child and missing meals is affecting their concentration in afternoon lessons.',
                    'My child {student} ({class}, Section {section}) found what appeared to be a piece of plastic in their meal today. {student} was very distressed. I have asked them to retain the item as evidence. I expect a formal response regarding quality controls.',
                    'I enrolled {student} in {class}, Section {section} at this school on the understanding that vegetarian options would be available daily. For the past two weeks there have been no vegetarian main course options at all. Please address this immediately.',
                    '{student} told me that the dining tables in their lunch sitting for {class}, Section {section} are consistently not cleaned between sittings. They showed me a photo on their phone. This is a basic hygiene issue that should have been caught by supervisory staff.',
                    'My child {student} ({class}, Section {section}) was charged for a meal they did not receive last Friday because the cafeteria had run out before the queue reached them. I would like a refund and for the school to review the serving arrangements for later lunch slots.',
                ],
                'teacher' => [
                    'A student in my class, {student} from {class} Section {section}, had an allergic reaction after eating cafeteria food during the lunch break today. I administered first aid and contacted the nurse immediately. I request that the cafeteria be informed of all documented dietary restrictions urgently.',
                    'Students in {class}, Section {section} have been consistently complaining about food quality this term. {student} and several classmates have started skipping lunch altogether, which is affecting their attention levels during the afternoon.',
                    '{student} in {class}, Section {section} has a documented dietary restriction that the cafeteria has failed to accommodate on three separate occasions. I have spoken to the cafeteria supervisor directly with no result. I am formally escalating this.',
                    'The dining area for {class}, Section {section}\'s lunch sitting was not cleaned before we arrived today. I observed visibly soiled tables and insufficient available seats. I raised it with the cafeteria supervisor but was told it was not their responsibility.',
                    'My class {class}, Section {section} has a late lunch slot which means {student} and other students arrive when most food is gone. They had very limited options today. I am requesting that the lunch allocation system be reviewed to ensure equitable access.',
                ],
            ],
            'facilities' => [
                'parent' => [
                    'My child {student} in {class}, Section {section} has mentioned that the classroom air conditioning has not been working for over three weeks. Given the current temperatures, the learning conditions are very poor. I would like to know when this will be repaired.',
                    'The drinking water dispenser near {class}, Section {section}\'s classroom has been broken for over a month according to {student}. Children are walking to the main building to get water, which is taking time out of lessons. Please prioritise this repair.',
                    'I visited the school last week to collect {student} ({class}, Section {section}) and noticed that the playground equipment near Block B is visibly damaged. One of the climbing bars is hanging loose. A child could be seriously injured.',
                    '{student} in {class}, Section {section} says the classroom projector has been broken all term and teachers cannot use digital materials. This is affecting the quality of lessons significantly. I would appreciate a timeline for when this will be fixed.',
                    'I am raising a hygiene concern about the restrooms adjacent to {class}, Section {section}\'s floor. {student} tells me they are often dirty and poorly maintained, and has started waiting until they get home to use the toilet, which cannot be good for their health.',
                    '{student} in {class}, Section {section} sits near a window that cannot be properly closed. Cold air and noise from outside are affecting {student}\'s focus on a daily basis. I am requesting the window latch be repaired as a matter of priority.',
                ],
                'teacher' => [
                    'The projector in my classroom — assigned to {class}, Section {section} — has been broken for three weeks. I have raised a maintenance request twice with no response. {student} and the rest of the group are missing digital content that is central to the current unit.',
                    'The science lab chemicals I am using with {class}, Section {section} are past their expiry date. {student} and classmates are conducting practical sessions with compromised materials. This is both a safety and an educational concern.',
                    'The chairs in my classroom are broken and several students including {student} in {class} Section {section} cannot sit comfortably for extended lessons. I have reported this to the facilities team twice this month already.',
                    'The shared printer on the teacher floor has been jammed for four days. I need to print exam papers for {class}, Section {section} by tomorrow. {student} and others will be sitting the test and delays in printing will disrupt the schedule.',
                    'My classroom window has been stuck open since last Thursday. The noise and cold are causing significant disruption to {class}, Section {section}. {student} sits near the window and has been particularly affected. I am requesting urgent maintenance support.',
                    'The fire extinguisher in my classroom ({class}, Section {section}) has not been inspected this academic year. Given that we run lab sessions with {student} and other students, this is an unacceptable safety risk. Please schedule an inspection immediately.',
                    'The storage room allocated to my subject area is currently inaccessible due to a broken lock. Key resources for {class}, Section {section} are inside, including workbooks for {student}\'s group. I cannot run tomorrow\'s lesson without access.',
                ],
            ],
            'transport' => [
                'parent' => [
                    'The school bus serving our area has been arriving 25 to 30 minutes late every morning this week. As a result, {student} in {class}, Section {section} is being marked late on arrival despite leaving home on time. Please liaise with the transport provider urgently.',
                    'My child {student} ({class}, Section {section}) was dropped at the wrong stop last Wednesday. I was waiting at the correct stop and received a frantic call from {student} who was at an unfamiliar location alone. This is a serious safeguarding concern and I expect a formal explanation.',
                    'The school bus route was changed without any notification to parents. {student} missed the bus on Monday because the stop had moved and nobody informed us. {student} is in {class}, Section {section} and relies on this bus every single day.',
                    'The bus {student} uses every morning ({class}, Section {section}) is overcrowded and children are standing for the full journey. I have spoken to two other parents who share this concern. This is unsafe and likely in breach of transport regulations.',
                    'The school bus did not arrive at all on Thursday morning. {student} ({class}, Section {section}) waited 40 minutes in the heat and I had to arrange alternative transport at short notice. No message or explanation was sent by the school or the driver.',
                    'I have noticed that the school bus carrying {student} ({class}, Section {section}) does not have functioning seatbelts on all seats. {student} has mentioned this several times. I am raising this formally as it is a safety issue requiring urgent rectification.',
                    '{student} in {class}, Section {section} missed the bus this morning because the departure time was changed without informing parents. {student} was left stranded and I only found out through another parent. Please ensure all changes to routes or times are communicated in advance.',
                ],
                'teacher' => [
                    'The transport for the field trip I organised for {class}, Section {section} has not been confirmed despite approval being granted three weeks ago. {student} and the other students have been told the trip is happening. I need confirmation of bus allocation before I can finalise the logistics.',
                    'Students from {class}, Section {section} arrived late to their external examination yesterday due to transport delays. {student} was visibly distressed on arrival. I am filing this formally as it may be used as context if any student wishes to appeal their result.',
                    'The bus schedule for the upcoming excursion was not shared with supervising staff until the morning of departure. I was responsible for {class}, Section {section} and had no information about pick-up times. This must not happen again.',
                    'I have requested confirmation of bus allocation for the sports day event involving {class}, Section {section} three times this week. {student} and the team members are asking me for logistics I do not have. No response has been received from the transport coordinator.',
                    'The transport plan for the annual trip involving {class}, Section {section} remains incomplete two days before departure. I have {student} and 24 other students expecting to travel on Friday. Multiple staff have followed up with no resolution.',
                ],
            ],
            'health' => [
                'parent' => [
                    'My child {student} in {class}, Section {section} has been showing signs of significant anxiety over the past month. They are not sleeping well, have lost their appetite, and cry before school each morning. I believe the school environment may be a contributing factor and I am requesting a meeting with the school counsellor.',
                    '{student} ({class}, Section {section}) was sent home unwell last Tuesday and I was not informed until over two hours after the incident. By the time I received the call, {student} had been sitting in the office alone for most of the afternoon. Please review the notification protocol.',
                    'My child {student} in {class}, Section {section} has a documented severe peanut allergy. I provided this information at enrolment and again at the start of this term. Despite this, peanut-based food is being served in the cafeteria without any warning. One incident could be life-threatening.',
                    'I was not notified that {student} ({class}, Section {section}) had a fall on the playground last week. I noticed a bruise when helping {student} change at home that evening. I would like a full incident report and confirmation of what first aid was administered.',
                    '{student} in {class}, Section {section} was given medication by the school nurse without our prior consent. I was not contacted beforehand despite our emergency contact number being on file. I would like an explanation and a review of the medication administration process.',
                    'My child {student} ({class}, Section {section}) has been experiencing frequent headaches at school. We believe the seating position near the whiteboard may be contributing to eye strain. I am requesting that {student}\'s seating be reviewed and that we are consulted before changes are made.',
                    '{student} has asthma and is in {class}, Section {section}. Over the past two weeks {student} has had increased symptoms attributed to dust in the classroom. I am requesting the room be properly cleaned and that {student}\'s inhaler protocol be reviewed with the nurse.',
                ],
                'teacher' => [
                    '{student} in {class}, Section {section} fainted during my lesson this morning. I administered basic first aid, called the office, and stayed with the student until the nurse arrived. I am filing this incident report formally and requesting a follow-up from the health team.',
                    '{student} in {class}, Section {section} disclosed self-harm concerns to me during a pastoral check-in this afternoon. I have followed the safeguarding protocol and notified the designated officer. I am submitting this formally to ensure it is logged and the counsellor is briefed.',
                    'Several students in {class}, Section {section} have been showing symptoms of illness over the past three days. {student} was among the first to report feeling unwell. I am requesting a health check and advising that parents be informed proactively before it spreads.',
                    '{student} in {class}, Section {section} has a confirmed diagnosis of epilepsy. They experienced a brief episode today during the second lesson. The existing medical action plan needs to be reviewed with the nurse — the protocol I was given last year is now out of date.',
                    'The humidity levels in my classroom ({class}, Section {section}) have been very high this week due to the broken ventilation unit. {student} has asthma and has used their inhaler twice during class. I am requesting that the ventilation issue be fixed as a matter of urgency.',
                    'I have a student, {student}, in {class} Section {section} who has disclosed significant mental health struggles over the past two weeks. They have been missing assignments and withdrawing from social interaction. I am requesting an immediate counsellor referral and parent notification.',
                    '{student} in {class}, Section {section} was involved in a playground incident today resulting in a minor injury. I completed a first aid form but want to ensure the incident is escalated to management and that parents are informed by the school before {student} gets home.',
                ],
            ],
            'communication' => [
                'parent' => [
                    'I have sent three messages to {student}\'s class teacher over the past two weeks regarding {class}, Section {section} and have not received any response. I am now raising this formally and requesting a callback within two working days.',
                    'I arrived to collect {student} ({class}, Section {section}) last Monday and found a new pick-up procedure had been put in place without any prior communication. {student} was waiting at the old location and was distressed. Please ensure parents are notified in advance of process changes.',
                    'The monthly parent communication newsletter has not been sent for the past two months. I have no information about upcoming events or schedule changes for {class}, Section {section}. I would like to understand why communication has stopped and when it will resume.',
                    'I requested an appointment with the principal three weeks ago to discuss concerns about {student} ({class}, Section {section}). I have had no acknowledgement, let alone a confirmed date. Please treat this as a formal escalation.',
                    'A staff member spoke to {student} ({class}, Section {section}) in a way that my child described as disrespectful and demeaning. {student} was very upset when they came home. I am raising this formally and requesting it be investigated.',
                    'I updated {student}\'s emergency contact information in writing two months ago. The school records still reflect the old details. This is concerning given {student} is in {class}, Section {section} and accurate records are critical in an emergency.',
                    'I was not informed about a school event that {student} ({class}, Section {section}) was supposed to attend last week. By the time I heard about it from another parent, it was too late. I would like to understand how communication is managed for parents who have not joined the class chat.',
                ],
                'teacher' => [
                    'I submitted a leave request two weeks ago and have not received any response from management. My absence affects {class}, Section {section}, and {student} and the other students need a confirmed substitute arrangement in place before I am absent.',
                    'A parent of {student} in {class}, Section {section} has been contacting me directly on my personal number at all hours. I have asked them to use the official school channel but they continue to do so. I am requesting the school communicate the official protocol to the parent formally.',
                    'The staff timetable change for next term was communicated with less than 24 hours\' notice. I teach {class}, Section {section} and the change directly affects lesson preparation for {student}\'s group. I am requesting adequate notice for any future scheduling adjustments.',
                    'I did not receive the updated staff handbook at the start of this term. I have been operating based on last year\'s policies for {class}, Section {section}. I only discovered there were changes when a parent of {student} raised a policy point I was unaware of.',
                    'A parent escalated a concern about {student} directly to the branch manager without speaking to me first. As {student}\'s class teacher in {class}, Section {section}, I should have been the first point of contact. I would like clarity on the escalation procedure so this does not recur.',
                    'The department meeting last week was rescheduled without notifying all staff. I missed it and have not received the minutes. Decisions were made that affect {class}, Section {section}, including changes to {student}\'s assessment schedule that I am only hearing about second-hand.',
                ],
            ],
            'it' => [
                'parent' => [
                    'My child {student} in {class}, Section {section} has been unable to access the student portal since the recent system update. They have missed homework submissions and cannot access their schedule. I have tried resetting the password but the portal still shows an error.',
                    'The parent app is not sending any push notifications. I missed a time-sensitive message about {student} ({class}, Section {section}) last week as a result. The last notification I received was over three weeks ago. Please advise on how to fix this.',
                    '{student} ({class}, Section {section}) submitted their online assignment before the deadline but it is showing as missing in the teacher\'s system. {student} has a screenshot of the submission confirmation. This needs to be corrected before any marks are affected.',
                    'The video call link for my scheduled parent-teacher meeting about {student} ({class}, Section {section}) was not working. I waited online for 20 minutes with no connection established. I would like the meeting rescheduled and a working link provided in advance.',
                    '{student}\'s account ({class}, Section {section}) was locked after one incorrect login attempt. The self-service password reset is also not functioning. {student} has now been locked out for five days and cannot access homework or grades.',
                    'The e-library access that {student} ({class}, Section {section}) was using for a research project was revoked without explanation. {student} is now unable to complete the assignment due next week. Please restore access or advise on an alternative.',
                    'The grade portal for {class}, Section {section} has not been updated since mid-term. {student} and I have no visibility of any assessments completed since then. I would like to know if this is a known issue and when it will be resolved.',
                ],
                'teacher' => [
                    'The smartboard in my classroom ({class}, Section {section}) has not been working since Monday morning. I have a formal observation scheduled for Thursday and {student}\'s group is in that lesson. This is urgent and needs same-day resolution if possible.',
                    'The grade entry system has logged me out twice mid-submission this week for {class}, Section {section}. On both occasions I lost the data I had entered, including {student}\'s scores. I cannot submit final marks until this is resolved.',
                    'The online assessment platform timed out during a live test for {class}, Section {section}. {student} and several other students lost their work mid-way through the exam. I need guidance on how to handle this from an assessment integrity perspective.',
                    'Student devices in my classroom are not connecting to the school Wi-Fi. This affects {class}, Section {section} including {student}, who relies on the tablet for differentiated materials. Digital lessons have been impossible to run for three days.',
                    'I cannot generate the end-of-term report for {class}, Section {section} because the report tool in the teacher portal is returning an error. {student}\'s individual report is among those outstanding and the submission deadline is Friday.',
                    'The virtual classroom link I set up for a remote session with {class}, Section {section} was broken during the lesson. {student} and most of the group were disconnected within ten minutes. I am requesting IT support to investigate and provide a reliable alternative.',
                ],
            ],
            'events' => [
                'parent' => [
                    'I did not receive an invitation to the prize-giving ceremony despite being told that {student} ({class}, Section {section}) is one of the award recipients. I found out through another parent the day before the event. Please ensure parents are informed directly and promptly in future.',
                    'The field trip permission slip deadline for {class}, Section {section} was never communicated to parents. {student} missed the trip because I did not submit consent in time — I was completely unaware of any deadline. I request that communication processes be improved.',
                    'I paid the deposit for {student}\'s school trip ({class}, Section {section}) over a month ago and have not received any confirmation, receipt, or details about the itinerary. Please issue a receipt and confirm whether {student} is registered.',
                    'The dress code for the cultural event was not communicated to parents of {class}, Section {section}. {student} arrived in regular school uniform while other students were in traditional dress. {student} was embarrassed and upset. Please ensure event requirements are shared at least one week in advance.',
                    '{student} in {class}, Section {section} was not informed about the change to sports practice timing. As a result, {student} missed two sessions and is now at risk of losing their place on the team. I am requesting the coaching staff contact us directly.',
                    'I registered {student} ({class}, Section {section}) for the after-school activity programme but the online system did not issue a confirmation. I am unsure whether {student} is enrolled. Please check the records and let me know.',
                ],
                'teacher' => [
                    'The sports day logistics for the event involving {class}, Section {section} have not been communicated to teaching staff. {student} and the rest of the team have been asking me for details I do not have. We are less than a week away and I still have no information.',
                    'I organised a field trip for {class}, Section {section} that was approved two months ago. Transport has still not been arranged. {student} and all students have been told the trip is happening. We depart in four days and there is no confirmed bus allocation.',
                    'The annual day rehearsal schedule conflicts with the exam preparation timetable I submitted for {class}, Section {section}. {student} and multiple students are being pulled from revision sessions. I am requesting an urgent timetable review.',
                    'Permission forms for the upcoming trip have not been prepared or distributed to {class}, Section {section}. {student} and other students cannot participate without signed consent. The trip is in eight days and this must be resolved immediately.',
                    'I submitted a cultural day activity plan for {class}, Section {section} over three weeks ago and have received no acknowledgement. {student} and the group have been preparing for a presentation and I need confirmation that it has been approved.',
                    'The sponsorship for the inter-school debate competition involving {student} from {class}, Section {section} has still not been confirmed. The registration deadline is in two days. Please escalate this to whoever has authority to approve the budget.',
                ],
            ],
            'general' => [
                'parent' => [
                    'I have tried to resolve this informally with the class teacher for {student} ({class}, Section {section}) but the matter remains unresolved after two weeks. I am now submitting formally so it is on record and can be tracked to completion.',
                    'My child {student} in {class}, Section {section} raised this concern with me last week. I spoke to the teacher informally but was told nothing could be done at that level. I am escalating through this system as advised.',
                    'This has been ongoing since the start of term for {student} in {class}, Section {section}. I would appreciate an acknowledgement of receipt and an estimated timeline for a response.',
                    '{student} ({class}, Section {section}) has been affected by this issue for several weeks. I expect this to be reviewed by someone with the authority to act and for me to be kept updated throughout.',
                    'I am a concerned parent of {student} in {class}, Section {section}. I value the school\'s open communication policy and hope this matter can be resolved professionally and without delay.',
                    'This is a follow-up to an issue I raised verbally two weeks ago regarding {student} ({class}, Section {section}). As I have not heard back, I am now submitting formally. Please treat this as a priority.',
                ],
                'teacher' => [
                    'I am submitting this formally after raising the matter verbally. It involves {student} in {class}, Section {section} directly and I want this on the official record so it can be tracked.',
                    'This issue has been affecting {student} in {class}, Section {section} for over two weeks. I have tried to resolve it at classroom level but require management support to proceed.',
                    'I am not certain which department owns this issue, but it is affecting {student} in {class}, Section {section} and needs to be routed appropriately. Please forward to the relevant person.',
                    'This is the second time I am raising this concern — the first report received no follow-up. {student} in {class}, Section {section} continues to be affected and I need a formal response.',
                    'The matter involves {student} in {class}, Section {section} and is affecting student welfare. I am requesting this be reviewed by the branch manager at the earliest opportunity.',
                ],
            ],
        ];

        $roleKey = $role === 'teacher' ? 'teacher' : 'parent';
        $pool    = $pools[$bucket][$roleKey] ?? $pools['general'][$roleKey];
        $raw     = $pool[array_rand($pool)];

        return strtr($raw, [
            '{student}' => $ctx['student'] ?? 'my child',
            '{class}'   => $ctx['class']   ?? 'their class',
            '{section}' => $ctx['section'] ?? 'their section',
        ]);
    }

    // ── Role-specific descriptions ───────────────────────────────────────────

    private function descriptionForRole(string $role): string
    {
        if ($role === 'teacher') {
            static $teacher = [
                'I am raising this formally as informal channels have not produced a result.',
                'This has been ongoing for over a week and is starting to affect the entire class.',
                'I have documented the incidents and can provide full details if required.',
                'This matter requires administrative support — I cannot resolve it within my classroom remit.',
                'Please advise on the correct procedure so I can follow it without delay.',
                'I raised this verbally in the last staff meeting but it still has not been actioned.',
                'This is affecting student learning outcomes and needs to be addressed urgently.',
                'I have already spoken to the student and the parents — now escalating to management.',
                'The issue recurs every week and a systemic fix is required, not a one-off response.',
                'Requesting written confirmation of the action taken so I can update my records.',
                'I am available to discuss this further at any time — please schedule a meeting.',
                'This is the second time I am submitting this — the first submission had no follow-up.',
                'I want this on record in case the situation escalates further.',
                'Other teachers in the department have reported the same concern independently.',
                'I have already tried coordinating with the relevant staff but have not had success.',
                'Please route this to the appropriate person — I am not sure who owns this issue.',
                'The student involved is generally well-behaved, so this situation is out of character.',
                'Urgently requesting support before the end of the current week.',
                'I have kept the parents informed throughout — they are awaiting school\'s response.',
                'This affects multiple students and warrants a whole-class or department-level response.',
                // Roman Urdu
                'Yeh masla kaafi dino se chal raha hai, please koi action liya jaye.',
                'Main ne verbally bhi bataya tha, ab formally record mein daalna chahta hun.',
                'Mujhe nahi pata exactly kiski zimmedari hai, please sahi jagah forward karein.',
                'Is hafte ke andar koi jawab chahiye warna mujhe principal ko directly batana hoga.',
                'Yeh sirf ek student ka masla nahi hai, poori class affect ho rahi hai.',
                'Student ka behavior change hua hai, lagta hai ghar ya school mein kuch aur bhi chal raha hai.',
            ];

            return $teacher[array_rand($teacher)];
        }

        static $parent = [
            'We have been experiencing this problem for over two weeks and need a prompt resolution.',
            'This matter is affecting my child\'s performance and well-being at school.',
            'Kindly look into this at your earliest convenience — we are happy to discuss further.',
            'I tried to resolve this directly but was unsuccessful. Please escalate if necessary.',
            'This is urgent and needs immediate attention from the administration.',
            'We would appreciate an update on the status of this issue as soon as possible.',
            'Please acknowledge this concern and let us know the next steps.',
            'We have noticed this issue recurring and feel it needs to be formally documented.',
            'My child mentioned this to the class teacher already, but no action has been taken.',
            'This has been going on since the beginning of the term and is becoming very concerning.',
            'I am raising this on behalf of my child who is too anxious to speak up directly.',
            'We have tried calling the school office but have been unable to reach anyone.',
            'I would appreciate a face-to-face meeting to discuss this in more detail.',
            'Other parents in our class group have reported the same issue.',
            'We trust the school will handle this professionally and keep us informed.',
            'My child has been very distressed and it is affecting their sleep and appetite.',
            'I want this recorded formally so that it can be tracked and followed up on.',
            'This is the second time I am raising this — the first time it was not resolved.',
            'I understand the school is busy but this requires attention before end of week.',
            'Please treat this as confidential — we do not want other families involved.',
            'We are happy to cooperate fully with any investigation the school conducts.',
            'Please confirm receipt and provide an estimated timeline for resolution.',
            'My child is otherwise very happy at school and I want to resolve this quickly.',
            'This seems systemic and may require a policy review, not just a one-off fix.',
            'I appreciate the school\'s efforts but feel this now needs to be escalated.',
            'We would like assurance that steps will be taken to prevent recurrence.',
            'My child came home upset today and I wanted to raise this immediately.',
            'The issue began after the class reshuffle and may be linked to that change.',
            'Please check with the relevant staff member and come back with their response.',
            'After three weeks with no resolution, we felt it was time to formally escalate.',
            'A quick call from the teacher or coordinator would reassure us greatly.',
            'I submitted this via email last week with no response, so I am submitting here.',
            'We value the school\'s open-door policy and hope for a sensitive resolution.',
            'My child has exams approaching, so speedy resolution is especially important.',
            'This is causing unnecessary stress for our entire family.',
            'We are not looking to cause trouble — just hoping for a fair resolution.',
            'If there is a formal complaints procedure, please let us know and we will follow it.',
            'Two other families have raised the same concern — a collective response would help.',
            'Please ensure this is reviewed by someone with the authority to act.',
            'My child asked me not to escalate, but as a parent I feel I must.',
            // Roman Urdu descriptions
            'Yeh masla kaafi arsay se chal raha hai, please jaldi se koi qadam uthayein.',
            'Mera bacha is wajah se school se darne laga hai, please help karein.',
            'Main ne class teacher ko bhi bataya tha, koi action nahi hua, isliye yahan submit kar raha hun.',
            'Please is baat ko seriously lein, mera bacha bahut pareshan hai.',
            'Agar school ki taraf se jald response nahi aaya to mujhe agey jaana hoga.',
            'Hum school ke saath mil ke kaam karna chahte hain, please guide farmayen.',
            'Is hafte ke andar koi update chahiye, warna situation aur kharab ho sakti hai.',
            'Mera bacha khud nahi bol sakta is liye main yahan complaint submit kar raha hun.',
            'Do hafte se yahi masla chal raha hai, koi hal nahi nikala gaya.',
            'Main ne phone kiya tha school mein, kisi ne nahi uthaya, isliye yahan likh raha hun.',
            'Kripya is matter ko confidential rakhein, hum nahi chahte doosre parents ko pata chale.',
            'Meri bachi raat ko sone se pehle roti hai, is liye yeh formally note karna zaruri tha.',
            'Yeh pehli dafa nahi hai, pehle bhi yahi hua tha, koi permanent hal chahiye.',
            'Main samajhta hun school busy hota hai, lekin yeh urgent matter hai please priority dein.',
        ];

        return $parent[array_rand($parent)];
    }

    // ── Name helpers ─────────────────────────────────────────────────────────

    private function randomStaffName(): string
    {
        static $first = ['Ahmed', 'Sara', 'Omar', 'Fatima', 'Ali', 'Noor', 'Khalid', 'Aisha',
                         'Yousef', 'Layla', 'Hassan', 'Maryam', 'Ibrahim', 'Zainab', 'Tariq',
                         'Hana', 'Samir', 'Rania', 'Faisal', 'Dina'];
        static $last  = ['Al-Hassan', 'Malik', 'Rahman', 'Qureshi', 'Siddiqui',
                         'Khan', 'Ali', 'Ahmed', 'Hussain', 'Shah'];

        return $first[array_rand($first)].' '.$last[array_rand($last)];
    }

    private function randomParentName(): string
    {
        static $first = [
            'Asif', 'Hira', 'Usman', 'Sana', 'Bilal', 'Amna', 'Waqas', 'Rabia',
            'Imran', 'Sidra', 'Adnan', 'Nadia', 'Kamran', 'Farah', 'Tariq',
            'Uzma', 'Shahid', 'Bushra', 'Naeem', 'Rukhsana', 'Waseem', 'Saima',
            'Aamir', 'Shazia', 'Zahid', 'Fouzia', 'Hamid', 'Zara', 'Rizwan', 'Ayesha',
            'Zubair', 'Nusrat', 'Pervez', 'Shaheen', 'Tanveer', 'Shabana',
        ];
        static $last = [
            'Khan', 'Malik', 'Qureshi', 'Chaudhry', 'Sheikh', 'Rana', 'Mirza',
            'Siddiqui', 'Hashmi', 'Butt', 'Bajwa', 'Cheema', 'Ansari', 'Rizvi',
            'Niazi', 'Aslam', 'Gondal', 'Mughal', 'Gillani', 'Bokhari',
        ];

        return $first[array_rand($first)].' '.$last[array_rand($last)];
    }

    private function randomTeacherName(): string
    {
        static $first = [
            'Nasreen', 'Khalida', 'Rubina', 'Fehmida', 'Tahira', 'Parveen', 'Zubeda',
            'Shabnam', 'Asma', 'Nighat', 'Samreen', 'Farzana', 'Rehana', 'Mehnaz',
            'Mudassar', 'Naveed', 'Arshad', 'Javed', 'Pervaiz', 'Ghulam', 'Iftikhar',
            'Maqsood', 'Rashid', 'Saleem', 'Azhar', 'Tanvir', 'Shafiq', 'Zafar',
        ];
        static $last = [
            'Khan', 'Malik', 'Qureshi', 'Chaudhry', 'Sheikh', 'Rana', 'Mirza',
            'Siddiqui', 'Hashmi', 'Butt', 'Bajwa', 'Ansari', 'Aslam', 'Gillani',
        ];

        return $first[array_rand($first)].' '.$last[array_rand($last)];
    }

    // ── Student context helpers ───────────────────────────────────────────────

    private function randomStudentName(): string
    {
        static $names = [
            'Aisha', 'Hamza', 'Zainab', 'Omar', 'Fatima', 'Yusuf', 'Maryam', 'Ali',
            'Noor', 'Ibrahim', 'Hana', 'Hassan', 'Layla', 'Khalid', 'Sana', 'Bilal',
            'Rania', 'Tariq', 'Amna', 'Saad', 'Hira', 'Usman', 'Rabia', 'Imran',
            'Sidra', 'Adnan', 'Nadia', 'Kamran', 'Farah', 'Zubair', 'Ayesha', 'Rizwan',
            'Dania', 'Faisal', 'Sara', 'Waqas', 'Mahnoor', 'Shahzaib', 'Nimra', 'Junaid',
            'Areeba', 'Asad', 'Khadija', 'Zaid', 'Sobia', 'Haris', 'Iqra', 'Shoaib',
            'Lubna', 'Danish',
        ];

        return $names[array_rand($names)];
    }

    private function randomClass(): string
    {
        static $classes = [
            'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
            'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10',
            'Class 5', 'Class 6', 'Class 7', 'Class 8',
            'Form 1', 'Form 2', 'Form 3',
            'KG-1', 'KG-2',
        ];

        return $classes[array_rand($classes)];
    }

    private function randomSection(): string
    {
        static $sections = ['A', 'B', 'C', 'D', 'Blue', 'Red', 'Green', 'Falcon', 'Eagle', 'Star'];

        return $sections[array_rand($sections)];
    }
}
