{{-- Shared form fields for subject-combinations create/edit.
     Expects: $subjects (all active), optional $subjectCombination.
     Formatter-safe: no Blade/PHP inside script blocks. --}}
@php
$editing = isset($subjectCombination);
$principalIds = collect(old('principal_ids', $editing ? $subjectCombination->subjects->where('pivot.is_principal', true)->pluck('id')->all() : []))->map(fn($id) => (int) $id)->all();
$subsidiaryIds = collect(old('subsidiary_ids', $editing ? $subjectCombination->subjects->where('pivot.is_principal', false)->pluck('id')->all() : []))->map(fn($id) => (int) $id)->all();
@endphp

<div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
            <input type="text" name="code" value="{{ old('code', $editing ? $subjectCombination->code : '') }}" required placeholder="e.g. PCM" class="w-full rounded-lg border-gray-300 shadow-sm text-sm uppercase">
            @error('code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $editing ? $subjectCombination->name : '') }}" required placeholder="e.g. Physics, Chemistry, Mathematics" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
            @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="is_active" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                <option value="1" {{ old('is_active', $editing ? (string) (int) $subjectCombination->is_active : '1') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $editing ? (string) (int) $subjectCombination->is_active : '1') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-800">Principal Subjects <span class="text-red-500">*</span></h3>
    <p class="text-xs text-gray-500 mt-0.5 mb-4">Select exactly 3. Graded A–F (6–0 points) on UACE-style reports.</p>
    @error('principal_ids')<p class="text-xs text-red-600 mb-2">{{ $message }}</p>@enderror
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        @foreach($subjects as $subject)
        <label class="flex items-center space-x-2 p-2 border rounded-lg hover:bg-gray-50 cursor-pointer text-sm">
            <input type="checkbox" name="principal_ids[]" value="{{ $subject->id }}" {{ in_array($subject->id, $principalIds) ? 'checked' : '' }} class="rounded text-blue-600 principal-check">
            <span>{{ $subject->name }}</span>
        </label>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-800">Subsidiary Subjects</h3>
    <p class="text-xs text-gray-500 mt-0.5 mb-4">Usually General Paper plus Sub-Mathematics or Sub-ICT. A pass earns 1 point (max 2).</p>
    @error('subsidiary_ids')<p class="text-xs text-red-600 mb-2">{{ $message }}</p>@enderror
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        @foreach($subjects as $subject)
        <label class="flex items-center space-x-2 p-2 border rounded-lg hover:bg-gray-50 cursor-pointer text-sm">
            <input type="checkbox" name="subsidiary_ids[]" value="{{ $subject->id }}" {{ in_array($subject->id, $subsidiaryIds) ? 'checked' : '' }} class="rounded text-indigo-600 subsidiary-check">
            <span>{{ $subject->name }}</span>
        </label>
        @endforeach
    </div>
</div>

<script>
    // Prevent picking the same subject as both principal and subsidiary.
    document.querySelectorAll('.principal-check, .subsidiary-check').forEach(function(box) {
        box.addEventListener('change', function() {
            if (!this.checked) return;
            const other = this.classList.contains('principal-check') ? '.subsidiary-check' : '.principal-check';
            document.querySelectorAll(other).forEach(function(o) {
                if (o.value === box.value) o.checked = false;
            });
        });
    });
</script>