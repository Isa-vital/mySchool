<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Staff;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\FeeType;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Grade;
use App\Models\GradingScale;
use App\Models\GradingScaleRange;
use App\Models\SubjectCombination;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\ReportCard;
use App\Services\UgandaGrading;
use App\Models\TimetableSlot;
use App\Models\Notice;
use App\Models\BookCategory;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\User;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // CHANGED: production may run without fakerphp/faker installed.
        // $faker = \Faker\Factory::create();
        $faker = class_exists(\Faker\Factory::class)
            ? \Faker\Factory::create()
            : new class {
                private bool $enforceUnique = false;
                private ?float $optionalProbability = null;
                private static array $uniqueValues = [];

                public function unique(): self
                {
                    $clone = clone $this;
                    $clone->enforceUnique = true;
                    return $clone;
                }

                public function optional(float $probability = 0.5): self
                {
                    $clone = clone $this;
                    $clone->optionalProbability = $probability;
                    return $clone;
                }

                public function dateTimeBetween(string $start, string $end): \DateTime
                {
                    $min = strtotime($start) ?: (time() - 86400 * 365);
                    $max = strtotime($end) ?: time();
                    if ($max < $min) {
                        $tmp = $min;
                        $min = $max;
                        $max = $tmp;
                    }

                    $timestamp = random_int($min, $max);
                    return (new \DateTime())->setTimestamp($timestamp);
                }

                public function numerify(string $mask): ?string
                {
                    if ($this->shouldReturnNull()) {
                        return null;
                    }

                    $value = preg_replace_callback('/#/', fn() => (string) random_int(0, 9), $mask);

                    if (! $this->enforceUnique) {
                        return $value;
                    }

                    $attempt = 0;
                    while (isset(self::$uniqueValues[$value]) && $attempt < 50) {
                        $value = preg_replace_callback('/#/', fn() => (string) random_int(0, 9), $mask);
                        $attempt++;
                    }

                    self::$uniqueValues[$value] = true;
                    return $value;
                }

                public function randomElement(array $items)
                {
                    if ($this->shouldReturnNull()) {
                        return null;
                    }

                    return $items[array_rand($items)];
                }

                public function randomFloat(int $decimals, float $min, float $max): ?float
                {
                    if ($this->shouldReturnNull()) {
                        return null;
                    }

                    return round($min + lcg_value() * ($max - $min), $decimals);
                }

                public function numberBetween(int $min = 0, int $max = 2147483647): ?int
                {
                    if ($this->shouldReturnNull()) {
                        return null;
                    }

                    return random_int($min, $max);
                }

                private function shouldReturnNull(): bool
                {
                    return $this->optionalProbability !== null && lcg_value() > $this->optionalProbability;
                }
            };

        $this->command->info('Seeding Ugandan sample data...');

        // ── Academic Years & Terms ──────────────────────────────
        $ay2025 = AcademicYear::create([
            'name' => '2025',
            'start_date' => '2025-02-03',
            'end_date' => '2025-12-05',
            'is_current' => false,
        ]);
        Term::insert([
            ['academic_year_id' => $ay2025->id, 'name' => 'Term I',   'start_date' => '2025-02-03', 'end_date' => '2025-05-02',  'is_current' => false, 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $ay2025->id, 'name' => 'Term II',  'start_date' => '2025-05-26', 'end_date' => '2025-08-22',  'is_current' => false, 'created_at' => now(), 'updated_at' => now()],
            ['academic_year_id' => $ay2025->id, 'name' => 'Term III', 'start_date' => '2025-09-15', 'end_date' => '2025-12-05',  'is_current' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $ay2026 = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-02-02',
            'end_date' => '2026-12-04',
            'is_current' => true,
        ]);
        $term1 = Term::create(['academic_year_id' => $ay2026->id, 'name' => 'Term I',   'start_date' => '2026-02-02', 'end_date' => '2026-05-01',  'is_current' => true]);
        $term2 = Term::create(['academic_year_id' => $ay2026->id, 'name' => 'Term II',  'start_date' => '2026-05-25', 'end_date' => '2026-08-21',  'is_current' => false]);
        $term3 = Term::create(['academic_year_id' => $ay2026->id, 'name' => 'Term III', 'start_date' => '2026-09-14', 'end_date' => '2026-12-04',  'is_current' => false]);

        $this->command->info('  ✓ Academic years & terms');

        // ── Nursery Classes (Baby, Middle, Top) ────────────────
        $nurseryNames = ['Baby Class', 'Middle Class', 'Top Class'];
        foreach ($nurseryNames as $idx => $nName) {
            $nc = SchoolClass::create([
                'name' => $nName,
                'code' => 'N' . ($idx + 1),
                'level' => $idx - 2, // -2, -1, 0 so they sort before P.1
                'category' => 'nursery',
                'description' => $nName,
                'is_active' => true,
            ]);
            Section::create(['school_class_id' => $nc->id, 'name' => 'A', 'capacity' => 30, 'is_active' => true]);
        }

        // ── Primary Classes (P.1 – P.7) ────────────────────────
        $primaryClasses = [];
        for ($i = 1; $i <= 7; $i++) {
            $c = SchoolClass::create([
                'name' => "P.$i",
                'code' => "P$i",
                'level' => $i,
                'category' => $i <= 4 ? 'lower_primary' : 'upper_primary',
                'description' => "Primary $i",
                'is_active' => true,
            ]);
            $primaryClasses[$i] = $c;
            // Streams
            foreach (['East', 'West'] as $stream) {
                Section::create([
                    'school_class_id' => $c->id,
                    'name' => $stream,
                    'capacity' => 50,
                    'is_active' => true,
                ]);
            }
        }

        // ── Secondary Classes (S.1 – S.6) ──────────────────────
        $secondaryClasses = [];
        for ($i = 1; $i <= 6; $i++) {
            $c = SchoolClass::create([
                'name' => "S.$i",
                'code' => "S$i",
                'level' => 7 + $i,
                'category' => $i <= 4 ? 'o_level' : 'a_level',
                'description' => "Senior $i",
                'is_active' => true,
            ]);
            $secondaryClasses[$i] = $c;
            foreach (['A', 'B'] as $stream) {
                Section::create([
                    'school_class_id' => $c->id,
                    'name' => $stream,
                    'capacity' => 45,
                    'is_active' => true,
                ]);
            }
        }
        $this->command->info('  ✓ Classes & sections');

        // ── Subjects ────────────────────────────────────────────
        $primarySubjects = [];
        foreach (
            [
                ['English', 'ENG', 'core'],
                ['Mathematics', 'MATH', 'core'],
                ['Science', 'SCI', 'core'],
                ['Social Studies', 'SST', 'core'],
                ['Luganda', 'LUG', 'core'],
                ['Religious Education', 'RE', 'core'],
                ['Creative Arts', 'CA', 'elective'],
                ['Physical Education', 'PE', 'elective'],
            ] as [$name, $code, $type]
        ) {
            $s = Subject::create(['name' => $name, 'code' => $code, 'type' => $type, 'is_active' => true]);
            $primarySubjects[] = $s;
        }

        $secondarySubjects = [];
        foreach (
            [
                ['English Language', 'ELNG', 'core'],
                ['Mathematics', 'MTHS', 'core'],
                ['Physics', 'PHY', 'core'],
                ['Chemistry', 'CHEM', 'core'],
                ['Biology', 'BIO', 'core'],
                ['History', 'HIST', 'core'],
                ['Geography', 'GEO', 'core'],
                ['Kiswahili', 'KSW', 'elective'],
                ['Agriculture', 'AGR', 'elective'],
                ['Computer Studies', 'ICT', 'elective'],
                ['Commerce', 'COM', 'elective'],
                ['Fine Art', 'FA', 'elective'],
            ] as [$name, $code, $type]
        ) {
            $s = Subject::create(['name' => $name, 'code' => $code, 'type' => $type, 'is_active' => true]);
            $secondarySubjects[] = $s;
        }

        // Attach subjects to classes
        foreach ($primaryClasses as $c) {
            $c->subjects()->attach(collect($primarySubjects)->pluck('id'));
        }
        foreach ($secondaryClasses as $c) {
            $c->subjects()->attach(collect($secondarySubjects)->pluck('id'));
        }
        $this->command->info('  ✓ Subjects');

        // ── Staff ───────────────────────────────────────────────
        $ugandanStaff = [
            ['first_name' => 'Joseph',   'last_name' => 'Ssemakula',  'gender' => 'Male',   'designation' => 'Head Teacher',    'department' => 'Administration', 'qualification' => 'M.Ed Makerere University'],
            ['first_name' => 'Grace',    'last_name' => 'Namutebi',   'gender' => 'Female', 'designation' => 'Deputy Head',     'department' => 'Administration', 'qualification' => 'B.Ed Kyambogo University'],
            ['first_name' => 'Robert',   'last_name' => 'Mukasa',     'gender' => 'Male',   'designation' => 'Director of Studies', 'department' => 'Academics', 'qualification' => 'B.Sc Education MUK'],
            ['first_name' => 'Florence', 'last_name' => 'Among',      'gender' => 'Female', 'designation' => 'Bursar',          'department' => 'Finance',        'qualification' => 'BBA Makerere UBS'],
            ['first_name' => 'Patrick',  'last_name' => 'Ochieng',    'gender' => 'Male',   'designation' => 'Teacher',         'department' => 'Sciences',       'qualification' => 'B.Sc Ed (Physics) Gulu University'],
            ['first_name' => 'Agnes',    'last_name' => 'Akello',     'gender' => 'Female', 'designation' => 'Teacher',         'department' => 'Languages',      'qualification' => 'B.A Ed (English) MUK'],
            ['first_name' => 'Moses',    'last_name' => 'Waiswa',     'gender' => 'Male',   'designation' => 'Teacher',         'department' => 'Mathematics',    'qualification' => 'Dip. Ed Shimoni PTC'],
            ['first_name' => 'Sarah',    'last_name' => 'Nalubega',   'gender' => 'Female', 'designation' => 'Teacher',         'department' => 'Humanities',     'qualification' => 'B.A Ed (History) Kyambogo'],
            ['first_name' => 'David',    'last_name' => 'Tumusiime',  'gender' => 'Male',   'designation' => 'Teacher',         'department' => 'Sciences',       'qualification' => 'B.Sc Ed (Chemistry) MUST'],
            ['first_name' => 'Harriet',  'last_name' => 'Birungi',    'gender' => 'Female', 'designation' => 'Teacher',         'department' => 'Primary',        'qualification' => 'Grade III Teacher Cert.'],
            ['first_name' => 'Ronald',   'last_name' => 'Kato',       'gender' => 'Male',   'designation' => 'Teacher',         'department' => 'Primary',        'qualification' => 'Grade III Teacher Cert.'],
            ['first_name' => 'Annet',    'last_name' => 'Nambi',      'gender' => 'Female', 'designation' => 'Teacher',         'department' => 'Primary',        'qualification' => 'Dip. Primary Ed. Kyambogo'],
            ['first_name' => 'Ivan',     'last_name' => 'Byaruhanga', 'gender' => 'Male',   'designation' => 'Teacher',         'department' => 'ICT',            'qualification' => 'B.IT Makerere University'],
            ['first_name' => 'Christine', 'last_name' => 'Auma',       'gender' => 'Female', 'designation' => 'Matron',          'department' => 'Welfare',        'qualification' => 'Dip. Social Work IUIU'],
            ['first_name' => 'George',   'last_name' => 'Odongo',     'gender' => 'Male',   'designation' => 'Games Master',    'department' => 'Sports',         'qualification' => 'Dip. Sports Science MUBS'],
            ['first_name' => 'Betty',    'last_name' => 'Nankya',     'gender' => 'Female', 'designation' => 'Librarian',       'department' => 'Library',        'qualification' => 'B.LISM East African S. of LIS'],
            ['first_name' => 'Julius',   'last_name' => 'Mpanga',     'gender' => 'Male',   'designation' => 'Secretary',       'department' => 'Administration', 'qualification' => 'Dip. Secretarial Studies'],
            ['first_name' => 'Esther',   'last_name' => 'Kyomugisha', 'gender' => 'Female', 'designation' => 'School Nurse',    'department' => 'Health',         'qualification' => 'Dip. Nursing Mulago'],
        ];

        $staffRecords = [];
        foreach ($ugandanStaff as $i => $s) {
            $staffRecords[] = Staff::create([
                'staff_number' => 'STF' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'first_name' => $s['first_name'],
                'last_name' => $s['last_name'],
                'gender' => $s['gender'],
                'date_of_birth' => $faker->dateTimeBetween('-55 years', '-25 years')->format('Y-m-d'),
                'phone' => '07' . $faker->numerify('########'),
                'email' => strtolower($s['first_name']) . '.' . strtolower($s['last_name']) . '@myschool.ug',
                'address' => $faker->randomElement(['Kampala', 'Wakiso', 'Mukono', 'Entebbe', 'Jinja', 'Mbarara']),
                'designation' => $s['designation'],
                'department' => $s['department'],
                'qualification' => $s['qualification'],
                'join_date' => $faker->dateTimeBetween('-8 years', '-1 year')->format('Y-m-d'),
                'employment_type' => $s['designation'] === 'Teacher' ? 'full-time' : $faker->randomElement(['full-time', 'full-time', 'contract']),
                'is_active' => true,
            ]);
        }
        $this->command->info('  ✓ Staff (' . count($staffRecords) . ')');

        // ── Guardians ───────────────────────────────────────────
        $ugandanGuardians = [
            ['first_name' => 'John',     'last_name' => 'Ssebaggala',   'relationship' => 'father',  'occupation' => 'Business Man',     'phone' => '0771234567'],
            ['first_name' => 'Mary',     'last_name' => 'Ssebaggala',   'relationship' => 'mother',  'occupation' => 'Teacher',          'phone' => '0782345678'],
            ['first_name' => 'Peter',    'last_name' => 'Muwanga',      'relationship' => 'father',  'occupation' => 'Engineer',         'phone' => '0703456789'],
            ['first_name' => 'Rose',     'last_name' => 'Muwanga',      'relationship' => 'mother',  'occupation' => 'Nurse',            'phone' => '0754567890'],
            ['first_name' => 'Charles',  'last_name' => 'Lwanga',       'relationship' => 'father',  'occupation' => 'Farmer',           'phone' => '0775678901'],
            ['first_name' => 'Juliet',   'last_name' => 'Lwanga',       'relationship' => 'mother',  'occupation' => 'Housewife',        'phone' => '0786789012'],
            ['first_name' => 'Richard',  'last_name' => 'Okello',       'relationship' => 'father',  'occupation' => 'Lawyer',           'phone' => '0707890123'],
            ['first_name' => 'Margaret', 'last_name' => 'Okello',       'relationship' => 'mother',  'occupation' => 'Accountant',       'phone' => '0758901234'],
            ['first_name' => 'Francis',  'last_name' => 'Tumwebaze',    'relationship' => 'father',  'occupation' => 'Mechanic',         'phone' => '0779012345'],
            ['first_name' => 'Janet',    'last_name' => 'Tumwebaze',    'relationship' => 'mother',  'occupation' => 'Market Vendor',    'phone' => '0780123456'],
            ['first_name' => 'Stephen',  'last_name' => 'Kabugo',       'relationship' => 'father',  'occupation' => 'Taxi Driver',      'phone' => '0701234567'],
            ['first_name' => 'Dorothy',  'last_name' => 'Kabugo',       'relationship' => 'mother',  'occupation' => 'Tailor',           'phone' => '0752345678'],
            ['first_name' => 'James',    'last_name' => 'Atim',         'relationship' => 'father',  'occupation' => 'Police Officer',   'phone' => '0773456789'],
            ['first_name' => 'Stella',   'last_name' => 'Atim',         'relationship' => 'mother',  'occupation' => 'Secretary',        'phone' => '0784567890'],
            ['first_name' => 'Emmanuel', 'last_name' => 'Mugisha',      'relationship' => 'uncle',   'occupation' => 'Doctor',           'phone' => '0705678901'],
            ['first_name' => 'Beatrice', 'last_name' => 'Nakabuye',     'relationship' => 'aunt',    'occupation' => 'Social Worker',    'phone' => '0756789012'],
            ['first_name' => 'Simon',    'last_name' => 'Opio',         'relationship' => 'father',  'occupation' => 'Boda Boda Rider',  'phone' => '0777890123'],
            ['first_name' => 'Alice',    'last_name' => 'Opio',         'relationship' => 'mother',  'occupation' => 'Hairdresser',      'phone' => '0788901234'],
            ['first_name' => 'Thomas',   'last_name' => 'Bbosa',        'relationship' => 'father',  'occupation' => 'Carpenter',        'phone' => '0709012345'],
            ['first_name' => 'Lillian',  'last_name' => 'Bbosa',        'relationship' => 'mother',  'occupation' => 'Shop Owner',       'phone' => '0750123456'],
        ];

        $guardianRecords = [];
        foreach ($ugandanGuardians as $g) {
            $guardianRecords[] = Guardian::create([
                'first_name'   => $g['first_name'],
                'last_name'    => $g['last_name'],
                'relationship' => $g['relationship'],
                'phone'        => $g['phone'],
                'alt_phone'    => '07' . $faker->numerify('########'),
                'email'        => strtolower($g['first_name']) . '.' . strtolower($g['last_name']) . '@gmail.com',
                'address'      => $faker->randomElement(['Ntinda, Kampala', 'Bweyogerere, Wakiso', 'Naalya, Wakiso', 'Bugolobi, Kampala', 'Kireka, Wakiso', 'Kira, Wakiso', 'Namugongo, Wakiso', 'Lubowa, Wakiso', 'Nansana, Wakiso', 'Entebbe']),
                'occupation'   => $g['occupation'],
                'national_id'  => 'CM' . $faker->numerify('#############'),
            ]);
        }
        $this->command->info('  ✓ Guardians (' . count($guardianRecords) . ')');

        // ── Students ────────────────────────────────────────────
        $boyFirstNames = ['Brian', 'Kevin', 'Samuel', 'Daniel', 'Joshua', 'Timothy', 'Andrew', 'Isaac', 'Nathan', 'Abel', 'Derrick', 'Edgar', 'Felix', 'Gilbert', 'Henry', 'Joel', 'Kenneth', 'Lawrence', 'Martin', 'Nelson', 'Oscar', 'Paul', 'Reagan', 'Raymond', 'Solomon', 'Trevor', 'Victor', 'William', 'Allan', 'Ben'];
        $girlFirstNames = ['Prossy', 'Sharon', 'Diana', 'Mercy', 'Irene', 'Ruth', 'Lydia', 'Gloria', 'Hope', 'Patience', 'Doreen', 'Edith', 'Faith', 'Gladys', 'Joy', 'Lillian', 'Maureen', 'Naomi', 'Olivia', 'Phionah', 'Rebecca', 'Sandra', 'Tracy', 'Vivian', 'Winnie', 'Zaituni', 'Amina', 'Brenda', 'Carol', 'Dorcas'];
        $lastNames = ['Mugisha', 'Nakamya', 'Ssempijja', 'Nantongo', 'Kabugo', 'Namuganza', 'Okot', 'Achola', 'Tusiime', 'Mubiru', 'Nambooze', 'Lubega', 'Kirabo', 'Kasule', 'Nsubuga', 'Wafula', 'Akello', 'Kalungi', 'Nabwire', 'Byaruhanga', 'Kemigisa', 'Serugo', 'Nabukeera', 'Odeke', 'Mpanga'];

        $allClasses = collect($primaryClasses)->merge(collect($secondaryClasses));
        $studentRecords = [];
        $studentIndex = 0;

        foreach ($allClasses as $level => $class) {
            $sections = Section::where('school_class_id', $class->id)->get();
            $studentsPerSection = ($class->level <= 7) ? 8 : 6; // more primary students

            foreach ($sections as $section) {
                for ($s = 0; $s < $studentsPerSection; $s++) {
                    $studentIndex++;
                    $gender = $faker->randomElement(['Male', 'Female']);
                    $firstName = $gender === 'Male'
                        ? $boyFirstNames[array_rand($boyFirstNames)]
                        : $girlFirstNames[array_rand($girlFirstNames)];
                    $lastName = $lastNames[array_rand($lastNames)];

                    $student = Student::create([
                        'admission_number' => 'ADM' . str_pad($studentIndex, 5, '0', STR_PAD_LEFT),
                        'lin' => 'UG' . $faker->unique()->numerify('##########'), // EMIS Learner Identification Number
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'other_names' => $faker->optional(0.3)->randomElement(['Mukisa', 'Kisakye', 'Babirye', 'Wasswa', 'Nabukeera', 'Kiggundu']),
                        'gender' => $gender,
                        'date_of_birth' => $faker->dateTimeBetween(
                            '-' . (5 + $class->level) . ' years',
                            '-' . (3 + $class->level) . ' years'
                        )->format('Y-m-d'),
                        'nationality' => 'Ugandan',
                        'religion' => $faker->randomElement(['Catholic', 'Protestant', 'Muslim', 'SDA', 'Pentecostal', 'Orthodox']),
                        'address' => $faker->randomElement(['Ntinda', 'Bukoto', 'Naalya', 'Kira', 'Namugongo', 'Kireka', 'Bweyogerere', 'Nansana', 'Entebbe', 'Mukono', 'Gayaza', 'Kasubi']),
                        'previous_school' => $faker->optional(0.4)->randomElement(['Bright Future PS', 'St. Joseph PS Naggalama', 'Kampala Parents School', 'Greenhill Academy', 'St. Mary\'s Kisubi', null]),
                        'admission_date' => $faker->dateTimeBetween('-3 years', '-1 month')->format('Y-m-d'),
                        'status' => 'active',
                        'boarding_status' => $class->level >= 8 ? $faker->randomElement(['day', 'boarding', 'boarding']) : 'day',
                    ]);

                    // Attach guardian (pair guardians by family)
                    $guardianPairIndex = ($studentIndex - 1) % 10;
                    $fatherIndex = $guardianPairIndex * 2;
                    $motherIndex = $fatherIndex + 1;
                    if (isset($guardianRecords[$fatherIndex])) {
                        DB::table('student_guardian')->insert([
                            'student_id' => $student->id,
                            'guardian_id' => $guardianRecords[$fatherIndex]->id,
                            'is_primary' => true,
                        ]);
                    }
                    if (isset($guardianRecords[$motherIndex])) {
                        DB::table('student_guardian')->insert([
                            'student_id' => $student->id,
                            'guardian_id' => $guardianRecords[$motherIndex]->id,
                            'is_primary' => false,
                        ]);
                    }

                    // Enrollment
                    Enrollment::create([
                        'student_id' => $student->id,
                        'school_class_id' => $class->id,
                        'section_id' => $section->id,
                        'academic_year_id' => $ay2026->id,
                        'roll_number' => $section->name . str_pad($s + 1, 3, '0', STR_PAD_LEFT),
                        'status' => 'active',
                    ]);

                    $studentRecords[] = ['student' => $student, 'class' => $class, 'section' => $section];
                }
            }
        }
        $this->command->info('  ✓ Students (' . count($studentRecords) . ') with enrollments & guardians');

        // ── Grading Scale (Uganda UNEB style) ───────────────────
        $gradingScale = GradingScale::create(['name' => 'Uganda UNEB Grading', 'is_default' => true]);
        $gradeRanges = [
            ['grade' => 'D1', 'min_mark' => 90, 'max_mark' => 100, 'grade_point' => 1, 'description' => 'Distinction'],
            ['grade' => 'D2', 'min_mark' => 80, 'max_mark' => 89,  'grade_point' => 2, 'description' => 'Distinction'],
            ['grade' => 'C3', 'min_mark' => 70, 'max_mark' => 79,  'grade_point' => 3, 'description' => 'Credit'],
            ['grade' => 'C4', 'min_mark' => 60, 'max_mark' => 69,  'grade_point' => 4, 'description' => 'Credit'],
            ['grade' => 'C5', 'min_mark' => 55, 'max_mark' => 59,  'grade_point' => 5, 'description' => 'Credit'],
            ['grade' => 'C6', 'min_mark' => 50, 'max_mark' => 54,  'grade_point' => 6, 'description' => 'Credit'],
            ['grade' => 'P7', 'min_mark' => 40, 'max_mark' => 49,  'grade_point' => 7, 'description' => 'Pass'],
            ['grade' => 'P8', 'min_mark' => 30, 'max_mark' => 39,  'grade_point' => 8, 'description' => 'Pass'],
            ['grade' => 'F9', 'min_mark' => 0,  'max_mark' => 29,  'grade_point' => 9, 'description' => 'Failure'],
        ];
        foreach ($gradeRanges as $r) {
            GradingScaleRange::create(array_merge($r, ['grading_scale_id' => $gradingScale->id]));
        }
        $this->command->info('  ✓ Grading scale (UNEB)');

        // ── Fee Types & Structures ──────────────────────────────
        $feeTypes = [];
        foreach (
            [
                'Tuition Fee',
                'Registration Fee',
                'Examination Fee',
                'Library Fee',
                'Computer Lab Fee',
                'Sports Fee',
                'Medical Fee',
                'PTA Contribution',
                'Boarding Fee',
                'Uniform Fee',
            ] as $ft
        ) {
            $feeTypes[$ft] = FeeType::create(['name' => $ft, 'is_active' => true]);
        }

        // Primary fee structures (per term)
        foreach ($primaryClasses as $c) {
            FeeStructure::create(['fee_type_id' => $feeTypes['Tuition Fee']->id,     'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => 350000, 'currency' => 'UGX']);
            FeeStructure::create(['fee_type_id' => $feeTypes['Examination Fee']->id,  'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => 30000, 'currency' => 'UGX']);
            FeeStructure::create(['fee_type_id' => $feeTypes['Library Fee']->id,      'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => 15000, 'currency' => 'UGX']);
            FeeStructure::create(['fee_type_id' => $feeTypes['Sports Fee']->id,       'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => 20000, 'currency' => 'UGX']);
            FeeStructure::create(['fee_type_id' => $feeTypes['PTA Contribution']->id, 'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => 25000, 'currency' => 'UGX']);
        }

        // Secondary fee structures (per term)
        foreach ($secondaryClasses as $c) {
            $tuition = ($c->level <= 10) ? 550000 : 750000; // A-level costs more
            FeeStructure::create(['fee_type_id' => $feeTypes['Tuition Fee']->id,       'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => $tuition, 'currency' => 'UGX']);
            FeeStructure::create(['fee_type_id' => $feeTypes['Examination Fee']->id,   'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => 50000, 'currency' => 'UGX']);
            FeeStructure::create(['fee_type_id' => $feeTypes['Computer Lab Fee']->id,  'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => 40000, 'currency' => 'UGX']);
            FeeStructure::create(['fee_type_id' => $feeTypes['Library Fee']->id,       'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => 25000, 'currency' => 'UGX']);
            FeeStructure::create(['fee_type_id' => $feeTypes['Medical Fee']->id,       'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => 30000, 'currency' => 'UGX']);
            FeeStructure::create(['fee_type_id' => $feeTypes['PTA Contribution']->id,  'school_class_id' => $c->id, 'academic_year_id' => $ay2026->id, 'term_id' => $term1->id, 'amount' => 35000, 'currency' => 'UGX']);
        }
        $this->command->info('  ✓ Fee types & structures');

        // ── Invoices & Payments (for first 40 students) ────────
        $admin = User::first();
        $invoiceCount = 0;
        $paymentCount = 0;
        foreach (array_slice($studentRecords, 0, 40) as $index => $rec) {
            $student = $rec['student'];
            $class = $rec['class'];

            $structures = FeeStructure::where('school_class_id', $class->id)
                ->where('academic_year_id', $ay2026->id)
                ->where('term_id', $term1->id)
                ->get();

            if ($structures->isEmpty()) continue;

            $totalAmount = $structures->sum('amount');
            $invoiceCount++;
            $invoice = Invoice::create([
                'invoice_number' => 'INV' . str_pad($invoiceCount, 6, '0', STR_PAD_LEFT),
                'student_id' => $student->id,
                'academic_year_id' => $ay2026->id,
                'term_id' => $term1->id,
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'balance' => $totalAmount,
                'status' => 'unpaid',
                'due_date' => '2026-02-28',
            ]);

            foreach ($structures as $fs) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'fee_type_id' => $fs->fee_type_id,
                    'description' => $fs->feeType->name ?? '',
                    'amount' => $fs->amount,
                ]);
            }

            // Some students have made payments
            $paymentChance = $faker->randomFloat(2, 0, 1);
            if ($paymentChance > 0.3) { // 70% have paid something
                $amountPaid = ($paymentChance > 0.7)
                    ? $totalAmount // fully paid
                    : round($totalAmount * $faker->randomFloat(2, 0.3, 0.8), -3); // partial

                $paymentCount++;
                Payment::create([
                    'receipt_number' => 'RCT' . str_pad($paymentCount, 6, '0', STR_PAD_LEFT),
                    'invoice_id' => $invoice->id,
                    'student_id' => $student->id,
                    'amount' => $amountPaid,
                    'payment_method' => $faker->randomElement(['cash', 'mobile_money', 'bank_transfer']),
                    'reference' => $faker->optional(0.5)->numerify('MM################'),
                    'payment_date' => $faker->dateTimeBetween('2026-02-02', '2026-02-12')->format('Y-m-d'),
                    'received_by' => $admin?->id,
                ]);

                $status = ($amountPaid >= $totalAmount) ? 'paid' : 'partial';
                $invoice->update([
                    'amount_paid' => $amountPaid,
                    'balance' => max(0, $totalAmount - $amountPaid),
                    'status' => $status,
                ]);
            }
        }
        $this->command->info("  ✓ Invoices ($invoiceCount) & payments ($paymentCount)");

        // ── Attendance (last 5 school days for all students) ────
        $schoolDays = [];
        $d = now()->copy();
        while (count($schoolDays) < 5) {
            $d->subDay();
            if ($d->isWeekday()) $schoolDays[] = $d->format('Y-m-d');
        }

        $attendanceBatch = [];
        foreach ($studentRecords as $rec) {
            foreach ($schoolDays as $day) {
                $attendanceBatch[] = [
                    'student_id' => $rec['student']->id,
                    'school_class_id' => $rec['class']->id,
                    'section_id' => $rec['section']->id,
                    'academic_year_id' => $ay2026->id,
                    'date' => $day,
                    'status' => $faker->randomElement(['present', 'present', 'present', 'present', 'present', 'present', 'present', 'present', 'absent', 'late']),
                    'marked_by' => $admin?->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        // Insert in chunks to avoid memory issues
        foreach (array_chunk($attendanceBatch, 500) as $chunk) {
            Attendance::insert($chunk);
        }
        $this->command->info('  ✓ Attendance (' . count($attendanceBatch) . ' records)');

        // ── Exams & Grades ──────────────────────────────────────
        $botExam = Exam::create([
            'name' => 'Beginning of Term I Exam 2026',
            'academic_year_id' => $ay2026->id,
            'term_id' => $term1->id,
            'start_date' => '2026-02-09',
            'end_date' => '2026-02-11',
            'description' => 'Diagnostic assessment for the beginning of Term I',
            'is_published' => true,
        ]);

        $gradeCount = 0;
        foreach ($studentRecords as $rec) {
            $student = $rec['student'];
            $class = $rec['class'];
            $subjects = ($class->level <= 7) ? $primarySubjects : $secondarySubjects;

            foreach ($subjects as $subject) {
                $marks = $faker->numberBetween(25, 98);
                $gradeLetter = 'F9';
                foreach ($gradeRanges as $r) {
                    if ($marks >= $r['min_mark'] && $marks <= $r['max_mark']) {
                        $gradeLetter = $r['grade'];
                        break;
                    }
                }
                Grade::create([
                    'exam_id' => $botExam->id,
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'school_class_id' => $class->id,
                    'marks_obtained' => $marks,
                    'grade_letter' => $gradeLetter,
                    'achievement_level' => UgandaGrading::achievementLevel($marks),
                    'graded_by' => $admin?->id,
                ]);
                $gradeCount++;
            }
        }
        $this->command->info("  ✓ Exam & grades ($gradeCount grade records)");

        // ── A-Level Subject Combinations ────────────────────────
        $subjByCode = collect($secondarySubjects)->keyBy('code');
        $combosDef = [
            ['PCM', 'Physics, Chemistry, Mathematics', ['PHY', 'CHEM', 'MTHS']],
            ['PCB', 'Physics, Chemistry, Biology',     ['PHY', 'CHEM', 'BIO']],
            ['HGC', 'History, Geography, Commerce',    ['HIST', 'GEO', 'COM']],
        ];
        $combos = [];
        foreach ($combosDef as [$code, $name, $codes]) {
            $combo = SubjectCombination::create(['code' => $code, 'name' => $name, 'level' => 'a_level', 'is_active' => true]);
            foreach ($codes as $sc) {
                if (isset($subjByCode[$sc])) {
                    $combo->subjects()->attach($subjByCode[$sc]->id, ['is_principal' => true]);
                }
            }
            $combos[] = $combo;
        }
        // Assign combinations to A-level (S.5 & S.6) enrolments
        $aLevelClassIds = collect($secondaryClasses)->filter(fn($c) => $c->level >= 12)->pluck('id');
        Enrollment::whereIn('school_class_id', $aLevelClassIds)->get()->each(function ($enr) use ($combos) {
            $enr->update(['subject_combination_id' => $combos[array_rand($combos)]->id]);
        });
        $this->command->info('  ✓ A-level subject combinations');

        // ── School Requirements (scholastic materials) ──────────
        $reqDefs = [
            ['Ream of Paper', 2, 'reams'],
            ['Toilet Paper', 4, 'rolls'],
            ['Broom', 1, 'pieces'],
            ['Liquid Soap', 2, 'litres'],
            ['Jik / Bleach', 1, 'litres'],
        ];
        foreach ($reqDefs as [$rName, $qty, $unit]) {
            Requirement::create([
                'name' => $rName,
                'academic_year_id' => $ay2026->id,
                'term_id' => $term1->id,
                'quantity' => $qty,
                'unit' => $unit,
                'is_active' => true,
            ]);
        }
        $this->command->info('  ✓ School requirements');

        // ── Report Card Remarks (for first 20 students) ─────────
        $conducts = ['Excellent', 'Very Good', 'Good', 'Fair'];
        $ctComments = ['A hardworking student. Keep it up.', 'Good effort, but can do better.', 'Needs to improve in sciences.', 'Excellent performance this term.'];
        $htComments = ['Promoted to the next class.', 'Well done, keep focused.', 'Put in more effort next term.', 'A promising learner.'];
        foreach (array_slice($studentRecords, 0, 20) as $rec) {
            $student = $rec['student'];
            $studentGrades = Grade::where('exam_id', $botExam->id)->where('student_id', $student->id)->pluck('marks_obtained');
            $total = $studentGrades->sum();
            $avg = $studentGrades->count() ? round($total / $studentGrades->count(), 2) : 0;
            ReportCard::create([
                'student_id' => $student->id,
                'exam_id' => $botExam->id,
                'total_marks' => $total,
                'average' => $avg,
                'conduct' => $conducts[array_rand($conducts)],
                'class_teacher_comment' => $ctComments[array_rand($ctComments)],
                'head_teacher_comment' => $htComments[array_rand($htComments)],
                'next_term_begins' => '2026-05-25',
            ]);
        }
        $this->command->info('  ✓ Report card remarks');

        // ── Timetable (Mon–Fri for S.1 A as example) ────────────
        $s1 = $secondaryClasses[1];
        $s1SectionA = Section::where('school_class_id', $s1->id)->where('name', 'A')->first();
        $teachers = Staff::where('designation', 'Teacher')->get();
        $timeSlots = [
            ['08:00', '08:40'],
            ['08:40', '09:20'],
            ['09:20', '10:00'],
            ['10:30', '11:10'],
            ['11:10', '11:50'],
            ['11:50', '12:30'],
            ['14:00', '14:40'],
            ['14:40', '15:20'],
        ];
        $rooms = ['Room 1', 'Room 2', 'Lab 1', 'Room 3', 'Hall', 'Computer Lab'];

        for ($day = 1; $day <= 5; $day++) {
            foreach ($timeSlots as $i => $slot) {
                $subj = $secondarySubjects[$i % count($secondarySubjects)];
                $teacher = $teachers->random();
                TimetableSlot::create([
                    'school_class_id' => $s1->id,
                    'section_id' => $s1SectionA?->id,
                    'subject_id' => $subj->id,
                    'staff_id' => $teacher->id,
                    'academic_year_id' => $ay2026->id,
                    'day_of_week' => $day,
                    'start_time' => $slot[0],
                    'end_time' => $slot[1],
                    'room' => $rooms[array_rand($rooms)],
                ]);
            }
        }
        $this->command->info('  ✓ Timetable (S.1 A, Mon–Fri)');

        // ── Notices ─────────────────────────────────────────────
        $notices = [
            ['title' => 'Welcome Back to Term I 2026', 'content' => "Dear Parents and Students,\n\nWelcome back to a new academic term. Term I officially opened on Monday 2nd February 2026. We look forward to a productive and enjoyable term.\n\nAll students are expected to report with their school requirements including stationery, uniform, and school fees.\n\nGod bless you.\n— The Head Teacher", 'target_audience' => 'all', 'is_published' => true, 'publish_date' => '2026-02-02'],
            ['title' => 'School Fees Payment Deadline', 'content' => "This is to remind all parents/guardians that school fees for Term I 2026 should be paid by 28th February 2026. Students with outstanding balances after this date will not be allowed to sit for exams.\n\nPayment can be made via:\n- Mobile Money: 0771234567 (School Account)\n- Bank: Stanbic Bank, A/C 9030012345678\n- Cash at the Bursar\'s Office\n\nThank you for your cooperation.", 'target_audience' => 'parents', 'is_published' => true, 'publish_date' => '2026-02-05'],
            ['title' => 'PTA Meeting Notice', 'content' => "A Parents-Teachers Association (PTA) meeting is scheduled for Saturday 21st February 2026 at 10:00 AM in the school hall. All parents are encouraged to attend.\n\nAgenda:\n1. Welcoming remarks\n2. Academic performance review (2025)\n3. Infrastructure development update\n4. School fees discussion\n5. Any other business\n\nLight refreshments will be served.", 'target_audience' => 'parents', 'is_published' => true, 'publish_date' => '2026-02-10'],
            ['title' => 'Staff Meeting - Curriculum Review', 'content' => "All teaching staff are reminded of the curriculum review meeting on Friday 13th February 2026 at 3:30 PM in the staffroom. Please come with your schemes of work for Term I.\n\nThe Director of Studies will chair the meeting.", 'target_audience' => 'staff', 'is_published' => true, 'publish_date' => '2026-02-11'],
            ['title' => 'Inter-House Sports Day', 'content' => "The annual Inter-House Sports Day will be held on Friday 27th February 2026. Students should come with their house colors:\n\n🔴 Red House - Lions\n🔵 Blue House - Eagles\n🟢 Green House - Rhinos\n🟡 Yellow House - Leopards\n\nEvents include: 100m, 200m, 400m, relay, long jump, high jump, football, netball.\n\nParents are welcome to attend!", 'target_audience' => 'all', 'is_published' => true, 'publish_date' => '2026-02-12'],
        ];

        foreach ($notices as $n) {
            Notice::create(array_merge($n, ['created_by' => $admin?->id]));
        }
        $this->command->info('  ✓ Notices (' . count($notices) . ')');

        // ── Library ─────────────────────────────────────────────
        $categories = [];
        foreach (['Textbooks', 'Fiction', 'Non-Fiction', 'Reference', 'Science', 'History', 'Religious', 'Ugandan Literature'] as $cat) {
            $categories[$cat] = BookCategory::create(['name' => $cat]);
        }

        $books = [
            ['title' => 'MK Primary Mathematics Book 5',            'author' => 'MK Publishers',         'isbn' => '9789970000001', 'publisher' => 'MK Publishers',       'publish_year' => 2022, 'category' => 'Textbooks',          'copies' => 30, 'shelf' => 'A1'],
            ['title' => 'Fountain Primary English Book 6',           'author' => 'Fountain Publishers',   'isbn' => '9789970000002', 'publisher' => 'Fountain Publishers', 'publish_year' => 2021, 'category' => 'Textbooks',          'copies' => 25, 'shelf' => 'A2'],
            ['title' => 'Understanding Physics for O-Level',         'author' => 'Halliday & Resnick',    'isbn' => '9789970000003', 'publisher' => 'Longhorn',            'publish_year' => 2020, 'category' => 'Science',            'copies' => 20, 'shelf' => 'B1'],
            ['title' => 'Comprehensive Chemistry for S1-S4',        'author' => 'G. Kakuru',             'isbn' => '9789970000004', 'publisher' => 'Longhorn Uganda',     'publish_year' => 2019, 'category' => 'Science',            'copies' => 18, 'shelf' => 'B2'],
            ['title' => 'Biology for East Africa',                  'author' => 'D.G. Mackean',          'isbn' => '9789970000005', 'publisher' => 'Hodder Education',    'publish_year' => 2018, 'category' => 'Science',            'copies' => 15, 'shelf' => 'B3'],
            ['title' => 'History of East Africa',                   'author' => 'Ogenga Otunnu',         'isbn' => '9789970000006', 'publisher' => 'Fountain Publishers', 'publish_year' => 2020, 'category' => 'History',            'copies' => 12, 'shelf' => 'C1'],
            ['title' => 'The River Between',                        'author' => 'Ngugi wa Thiong\'o',    'isbn' => '9789970000007', 'publisher' => 'Heinemann',           'publish_year' => 1965, 'category' => 'Fiction',            'copies' => 20, 'shelf' => 'D1'],
            ['title' => 'Things Fall Apart',                        'author' => 'Chinua Achebe',         'isbn' => '9789970000008', 'publisher' => 'Heinemann',           'publish_year' => 1958, 'category' => 'Fiction',            'copies' => 22, 'shelf' => 'D2'],
            ['title' => 'Abyssinian Chronicles',                    'author' => 'Moses Isegawa',         'isbn' => '9789970000009', 'publisher' => 'Picador Africa',      'publish_year' => 2000, 'category' => 'Ugandan Literature', 'copies' => 10, 'shelf' => 'D3'],
            ['title' => 'Song of Lawino',                           'author' => 'Okot p\'Bitek',         'isbn' => '9789970000010', 'publisher' => 'East African Pub.',   'publish_year' => 1966, 'category' => 'Ugandan Literature', 'copies' => 15, 'shelf' => 'D4'],
            ['title' => 'The Concise Oxford English Dictionary',     'author' => 'Oxford',                'isbn' => '9789970000011', 'publisher' => 'OUP',                 'publish_year' => 2020, 'category' => 'Reference',          'copies' => 5,  'shelf' => 'E1'],
            ['title' => 'Holy Bible (Good News Edition)',            'author' => 'Bible Society',         'isbn' => '9789970000012', 'publisher' => 'Bible Society Uganda', 'publish_year' => 2015, 'category' => 'Religious',          'copies' => 10, 'shelf' => 'F1'],
            ['title' => 'Understanding Agriculture for S1-S4',      'author' => 'J. Tumuhairwe',         'isbn' => '9789970000013', 'publisher' => 'MK Publishers',       'publish_year' => 2021, 'category' => 'Textbooks',          'copies' => 14, 'shelf' => 'A3'],
            ['title' => 'ICT for Uganda Secondary Schools',         'author' => 'S. Okunna',             'isbn' => '9789970000014', 'publisher' => 'Longhorn Uganda',     'publish_year' => 2023, 'category' => 'Textbooks',          'copies' => 20, 'shelf' => 'A4'],
            ['title' => 'Kintu',                                    'author' => 'Jennifer Nansubuga Makumbi', 'isbn' => '9789970000015', 'publisher' => 'Transit Books',  'publish_year' => 2018, 'category' => 'Ugandan Literature', 'copies' => 8,  'shelf' => 'D5'],
        ];

        $bookRecords = [];
        foreach ($books as $b) {
            $bookRecords[] = Book::create([
                'title' => $b['title'],
                'author' => $b['author'],
                'isbn' => $b['isbn'],
                'publisher' => $b['publisher'],
                'publish_year' => $b['publish_year'],
                'book_category_id' => $categories[$b['category']]->id,
                'total_copies' => $b['copies'],
                'available_copies' => $b['copies'],
                'shelf_location' => $b['shelf'],
                'is_active' => true,
            ]);
        }

        // Issue some books
        $issuedCount = 0;
        foreach (array_slice($studentRecords, 0, 12) as $rec) {
            $book = $bookRecords[array_rand($bookRecords)];
            if ($book->available_copies <= 0) continue;

            BookIssue::create([
                'book_id' => $book->id,
                'borrower_type' => 'App\\Models\\Student',
                'borrower_id' => $rec['student']->id,
                'issue_date' => $faker->dateTimeBetween('2026-02-03', '2026-02-10')->format('Y-m-d'),
                'due_date' => '2026-02-24',
                'status' => 'issued',
                'issued_by' => $admin?->id,
            ]);
            $book->decrement('available_copies');
            $issuedCount++;
        }
        $this->command->info("  ✓ Library (" . count($bookRecords) . " books, $issuedCount issued)");

        $this->command->info('');
        $this->command->info('✅ All Ugandan sample data seeded successfully!');
        $this->command->info("   Students: " . count($studentRecords));
        $this->command->info("   Staff: " . count($staffRecords));
        $this->command->info("   Guardians: " . count($guardianRecords));
    }
}
