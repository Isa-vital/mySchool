<?php

use App\Models\Subject;
use App\Models\SubjectCombination;
use Illuminate\Database\Migrations\Migration;

// Seeds UACE subsidiary subjects and the common Uganda A-Level combinations.
// Idempotent: existing subjects/combinations are reused, never modified.
return new class extends Migration
{
    public function up(): void
    {
        $subjectIds = [];
        $ensureSubject = function (string $name, string $code) use (&$subjectIds) {
            $subject = Subject::where('code', $code)->orWhere('name', $name)->first()
                ?? Subject::create(['name' => $name, 'code' => $code, 'type' => 'core', 'is_active' => true]);
            $subjectIds[$code] = $subject->id;

            return $subject;
        };

        // Subsidiaries (GP compulsory for all combinations)
        $ensureSubject('General Paper', 'GP');
        $ensureSubject('Subsidiary Mathematics', 'SMTC');
        $ensureSubject('Subsidiary ICT', 'SICT');

        // Principal subjects used by the standard combinations
        $ensureSubject('Physics', 'PHY');
        $ensureSubject('Chemistry', 'CHE');
        $ensureSubject('Biology', 'BIO');
        $ensureSubject('Mathematics', 'MTC');
        $ensureSubject('History', 'HIS');
        $ensureSubject('Economics', 'ECO');
        $ensureSubject('Geography', 'GEO');
        $ensureSubject('Literature in English', 'LIT');
        $ensureSubject('Entrepreneurship', 'ENT');
        $ensureSubject('Divinity', 'DIV');
        $ensureSubject('Fine Art', 'ART');
        $ensureSubject('Agriculture', 'AGR');

        // code => [name, [principal codes], [subsidiary codes]]
        // Science combos take Sub-ICT; arts combos take Sub-Math; Math principals take Sub-ICT.
        $combinations = [
            'PCM' => ['Physics, Chemistry, Mathematics', ['PHY', 'CHE', 'MTC'], ['GP', 'SICT']],
            'PCB' => ['Physics, Chemistry, Biology', ['PHY', 'CHE', 'BIO'], ['GP', 'SMTC']],
            'BCM' => ['Biology, Chemistry, Mathematics', ['BIO', 'CHE', 'MTC'], ['GP', 'SICT']],
            'PEM' => ['Physics, Economics, Mathematics', ['PHY', 'ECO', 'MTC'], ['GP', 'SICT']],
            'BAG' => ['Biology, Agriculture, Geography', ['BIO', 'AGR', 'GEO'], ['GP', 'SMTC']],
            'HEG' => ['History, Economics, Geography', ['HIS', 'ECO', 'GEO'], ['GP', 'SMTC']],
            'HEL' => ['History, Economics, Literature', ['HIS', 'ECO', 'LIT'], ['GP', 'SMTC']],
            'HGL' => ['History, Geography, Literature', ['HIS', 'GEO', 'LIT'], ['GP', 'SMTC']],
            'MEG' => ['Mathematics, Economics, Geography', ['MTC', 'ECO', 'GEO'], ['GP', 'SICT']],
            'DEG' => ['Divinity, Economics, Geography', ['DIV', 'ECO', 'GEO'], ['GP', 'SMTC']],
            'HED' => ['History, Economics, Divinity', ['HIS', 'ECO', 'DIV'], ['GP', 'SMTC']],
            'AEG' => ['Art, Economics, Geography', ['ART', 'ECO', 'GEO'], ['GP', 'SMTC']],
        ];

        foreach ($combinations as $code => [$name, $principals, $subsidiaries]) {
            if (SubjectCombination::where('code', $code)->exists()) {
                continue;
            }

            $combination = SubjectCombination::create([
                'code' => $code,
                'name' => $name,
                'level' => 'a_level',
                'is_active' => true,
            ]);

            $payload = [];
            foreach ($principals as $subjectCode) {
                $payload[$subjectIds[$subjectCode]] = ['is_principal' => true];
            }
            foreach ($subsidiaries as $subjectCode) {
                $payload[$subjectIds[$subjectCode]] = ['is_principal' => false];
            }
            $combination->subjects()->sync($payload);
        }
    }

    public function down(): void
    {
        // Seed data — intentionally not reversed (schools may have customized it).
    }
};
