<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Register New Student</h2>
            <a href="{{ route('students.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to Students</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Student Info --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Student Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Other Names</label>
                    <input type="text" name="other_names" value="{{ old('other_names') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admission Number <span class="text-red-500">*</span></label>
                    {{-- CHANGED: auto-generated, sequential, read-only (server-side authoritative) --}}
                    <input type="text" name="admission_number" value="{{ old('admission_number', $nextAdmissionNumber ?? '') }}" readonly class="w-full rounded-lg border-gray-300 bg-gray-100 shadow-sm text-sm cursor-not-allowed">
                    <p class="text-xs text-gray-400 mt-1">Auto-generated</p>
                    @error('admission_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">LIN (EMIS Number)</label>
                    <input type="text" name="lin" value="{{ old('lin') }}" placeholder="Learner Identification Number" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('lin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UNEB Index Number</label>
                    <input type="text" name="uneb_index_number" value="{{ old('uneb_index_number') }}" placeholder="e.g. U0001/042" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('uneb_index_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <select name="gender" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Religion</label>
                    <input type="text" name="religion" value="{{ old('religion') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Blood Group</label>
                    <select name="blood_group" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admission Date</label>
                    <input type="date" name="admission_date" value="{{ old('admission_date', date('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Boarding Status</label>
                    <select name="boarding_status" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="day" {{ old('boarding_status', 'day') === 'day' ? 'selected' : '' }}>Day Scholar</option>
                        <option value="boarding" {{ old('boarding_status') === 'boarding' ? 'selected' : '' }}>Boarding</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('address') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medical Conditions</label>
                    <textarea name="medical_conditions" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('medical_conditions') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Previous School --}}
        {{-- CHANGED: dedicated previous school section (name, grade, attachment) --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Previous School</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Name</label>
                    <input type="text" name="previous_school" value="{{ old('previous_school') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('previous_school') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade / Class Attended</label>
                    <input type="text" name="previous_school_grade" value="{{ old('previous_school_grade') }}" placeholder="e.g. P.6" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('previous_school_grade') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attachment <span class="text-gray-400">(report card / transfer letter)</span></label>
                    <input type="file" name="previous_school_attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('previous_school_attachment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Enrollment --}}
        @if($academicYear)
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Enrollment ({{ $academicYear->name }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                    <select name="class_id" id="class_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" onchange="updateSections()">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" data-sections='@json($class->sections)' data-category="{{ $class->category() }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                    <select name="section_id" id="section_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select Section</option>
                    </select>
                </div>
                {{-- A-Level only: UACE subject combination (required for S.5/S.6) --}}
                <div id="combination_field" class="hidden md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Combination <span class="text-red-500">*</span></label>
                    <select name="subject_combination_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select Combination</option>
                        @foreach($combinations as $combination)
                        <option value="{{ $combination->id }}" {{ old('subject_combination_id') == $combination->id ? 'selected' : '' }}>{{ $combination->code }} — {{ $combination->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_combination_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
        @endif

        {{-- Guardian --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Guardian / Parent Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" name="guardian_first_name" value="{{ old('guardian_first_name') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" name="guardian_last_name" value="{{ old('guardian_last_name') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                    <select name="guardian_relationship" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        <option value="father" {{ old('guardian_relationship') === 'father' ? 'selected' : '' }}>Father</option>
                        <option value="mother" {{ old('guardian_relationship') === 'mother' ? 'selected' : '' }}>Mother</option>
                        <option value="uncle" {{ old('guardian_relationship') === 'uncle' ? 'selected' : '' }}>Uncle</option>
                        <option value="aunt" {{ old('guardian_relationship') === 'aunt' ? 'selected' : '' }}>Aunt</option>
                        <option value="guardian" {{ old('guardian_relationship') === 'guardian' ? 'selected' : '' }}>Guardian</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="guardian_email" value="{{ old('guardian_email') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                    <input type="text" name="guardian_occupation" value="{{ old('guardian_occupation') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="guardian_address" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('guardian_address') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('students.index') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Register Student</button>
        </div>
    </form>

    <script>
        function updateSections() {
            const classSelect = document.getElementById('class_id');
            const sectionSelect = document.getElementById('section_id');
            const selected = classSelect.options[classSelect.selectedIndex];
            const sections = selected ? JSON.parse(selected.dataset.sections || '[]') : [];

            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            sections.forEach(s => {
                sectionSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });

            // Combination applies to A-Level classes only.
            const combinationField = document.getElementById('combination_field');
            if (combinationField) {
                const isALevel = selected && selected.dataset.category === 'a_level';
                combinationField.classList.toggle('hidden', !isALevel);
                if (!isALevel) combinationField.querySelector('select').value = '';
            }
        }
        document.addEventListener('DOMContentLoaded', updateSections);
    </script>
</x-app-layout>