<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Student: {{ $student->full_name }}</h2>
            <a href="{{ route('students.show', $student) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Student Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Other Names</label>
                    <input type="text" name="other_names" value="{{ old('other_names', $student->other_names) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admission Number <span class="text-red-500">*</span></label>
                    <input type="text" name="admission_number" value="{{ old('admission_number', $student->admission_number) }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('admission_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">LIN (EMIS Number)</label>
                    <input type="text" name="lin" value="{{ old('lin', $student->lin) }}" placeholder="Learner Identification Number" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('lin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UNEB Index Number</label>
                    <input type="text" name="uneb_index_number" value="{{ old('uneb_index_number', $student->uneb_index_number) }}" placeholder="e.g. U0001/042" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('uneb_index_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <select name="gender" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        <option value="male" {{ old('gender', $student->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $student->gender) === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        @foreach(['active','graduated','transferred','withdrawn','suspended'] as $s)
                        <option value="{{ $s }}" {{ old('status', $student->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $student->nationality) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Religion</label>
                    <input type="text" name="religion" value="{{ old('religion', $student->religion) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Blood Group</label>
                    <select name="blood_group" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" {{ old('blood_group', $student->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $student->email) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admission Date</label>
                    <input type="date" name="admission_date" value="{{ old('admission_date', $student->admission_date?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Boarding Status</label>
                    <select name="boarding_status" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="day" {{ old('boarding_status', $student->boarding_status) === 'day' ? 'selected' : '' }}>Day Scholar</option>
                        <option value="boarding" {{ old('boarding_status', $student->boarding_status) === 'boarding' ? 'selected' : '' }}>Boarding</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('address', $student->address) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                    @if($student->photo)
                    <div class="mb-2">
                        <img src="{{ Storage::url($student->photo) }}" class="w-16 h-16 rounded-lg object-cover" alt="">
                    </div>
                    @endif
                    <input type="file" name="photo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medical Conditions</label>
                    <textarea name="medical_conditions" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('medical_conditions', $student->medical_conditions) }}</textarea>
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
                    <input type="text" name="previous_school" value="{{ old('previous_school', $student->previous_school) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('previous_school') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade / Class Attended</label>
                    <input type="text" name="previous_school_grade" value="{{ old('previous_school_grade', $student->previous_school_grade) }}" placeholder="e.g. P.6" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('previous_school_grade') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attachment <span class="text-gray-400">(report card / transfer letter)</span></label>
                    @if($student->previous_school_attachment)
                    <a href="{{ Storage::url($student->previous_school_attachment) }}" target="_blank" class="block text-xs mb-1 hover:underline" style="color: var(--primary-color);">View current attachment</a>
                    @endif
                    <input type="file" name="previous_school_attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('previous_school_attachment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- A-Level only: combination is stored on the current enrollment --}}
        @if($currentEnrollment && $currentEnrollment->schoolClass?->category() === 'a_level')
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">A-Level Combination ({{ $currentEnrollment->schoolClass->name }})</h3>
            <div class="md:w-1/2">
                <select name="subject_combination_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Combination</option>
                    @foreach($combinations as $combination)
                    <option value="{{ $combination->id }}" {{ old('subject_combination_id', $currentEnrollment->subject_combination_id) == $combination->id ? 'selected' : '' }}>{{ $combination->code }} — {{ $combination->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('students.show', $student) }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Update Student</button>
        </div>
    </form>
</x-app-layout>