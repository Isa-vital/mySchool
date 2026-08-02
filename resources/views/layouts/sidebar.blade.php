{{-- Sidebar Navigation --}}
<aside
    class="fixed inset-y-0 left-0 z-40 bg-white border-r border-gray-200 shadow-sm transition-all duration-300 overflow-y-auto"
    :class="{
        'w-64': sidebarOpen,
        'w-16': !sidebarOpen,
        '-translate-x-full lg:translate-x-0': !mobileSidebarOpen,
        'translate-x-0': mobileSidebarOpen
    }">
    {{-- Logo / School Name --}}
    <div class="flex items-center h-16 px-4 border-b border-gray-200" style="background-color: var(--primary-color);">
        @if(setting('school_logo'))
        <img src="{{ asset('storage/' . setting('school_logo')) }}" alt="Logo" class="h-10 w-10 rounded-full object-cover flex-shrink-0">
        @else
        <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
        </div>
        @endif
        <span class="ml-3 text-white font-bold text-sm truncate" x-show="sidebarOpen" x-transition>
            {{ setting('school_name', 'MySchool') }}
        </span>
    </div>

    {{-- Navigation Links --}}
    <nav class="mt-4 px-2 space-y-1">
        {{-- Dashboard --}}
        @can('dashboard.view')
        <x-sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home">
            Dashboard
        </x-sidebar-link>
        @endcan

        {{-- Students & Staff --}}
        @canany(['students.view', 'staff.view', 'guardians.view'])
        <x-sidebar-group label="People" icon="users" :active="request()->routeIs('students.*', 'staff.*', 'guardians.*')">
            @can('students.view')
            <x-sidebar-sublink href="{{ route('students.index') }}" :active="request()->routeIs('students.*')">Students</x-sidebar-sublink>
            @endcan
            @can('staff.view')
            <x-sidebar-sublink href="{{ route('staff.index') }}" :active="request()->routeIs('staff.*')">Staff</x-sidebar-sublink>
            @endcan
            @can('guardians.view')
            <x-sidebar-sublink href="{{ route('guardians.index') }}" :active="request()->routeIs('guardians.*')">Guardians</x-sidebar-sublink>
            @endcan
        </x-sidebar-group>
        @endcanany

        {{-- Academics --}}
        @canany(['academic_years.view', 'classes.view', 'subjects.view', 'timetable.view'])
        <x-sidebar-group label="Academics" icon="academic-cap" :active="request()->routeIs('academic-years.*', 'classes.*', 'sections.*', 'subjects.*', 'subject-combinations.*', 'timetable.*')">
            @can('academic_years.view')
            <x-sidebar-sublink href="{{ route('academic-years.index') }}" :active="request()->routeIs('academic-years.*')">Academic Years</x-sidebar-sublink>
            @endcan
            @can('classes.view')
            <x-sidebar-sublink href="{{ route('classes.index') }}" :active="request()->routeIs('classes.*')">Classes & Sections</x-sidebar-sublink>
            @endcan
            @can('subjects.view')
            <x-sidebar-sublink href="{{ route('subjects.index') }}" :active="request()->routeIs('subjects.*')">Subjects</x-sidebar-sublink>
            @endcan
            @can('subjects.view')
            <x-sidebar-sublink href="{{ route('subject-combinations.index') }}" :active="request()->routeIs('subject-combinations.*')">A-Level Combinations</x-sidebar-sublink>
            @endcan
            @can('timetable.view')
            <x-sidebar-sublink href="{{ route('timetable.index') }}" :active="request()->routeIs('timetable.*')">Timetable</x-sidebar-sublink>
            @endcan
        </x-sidebar-group>
        @endcanany

        {{-- Attendance --}}
        @canany(['attendance.view', 'attendance.mark'])
        <x-sidebar-link href="{{ route('attendance.index') }}" :active="request()->routeIs('attendance.*')" icon="clipboard-check">
            Attendance
        </x-sidebar-link>
        @endcanany

        {{-- Exams & Grades --}}
        @canany(['exams.view', 'grades.view', 'report_cards.view'])
        <x-sidebar-group label="Examinations" icon="document-text" :active="request()->routeIs('exams.*', 'grades.*', 'report-cards.*', 'uace-review.*', 'uace-cycles.*')">
            @can('exams.view')
            <x-sidebar-sublink href="{{ route('exams.index') }}" :active="request()->routeIs('exams.*')">Exams</x-sidebar-sublink>
            @endcan
            @can('grades.view')
            <x-sidebar-sublink href="{{ route('grades.index') }}" :active="request()->routeIs('grades.*')">Grades / Marks</x-sidebar-sublink>
            @endcan
            {{-- CHANGED (UACE paper rebuild): manual review queue + versioned rulesets --}}
            @can('grades.edit')
            <x-sidebar-sublink href="{{ route('uace-review.index') }}" :active="request()->routeIs('uace-review.*')">UACE Review Queue</x-sidebar-sublink>
            @endcan
            @can('settings.view')
            <x-sidebar-sublink href="{{ route('uace-cycles.index') }}" :active="request()->routeIs('uace-cycles.*')">UACE Rulesets</x-sidebar-sublink>
            @endcan
            @can('report_cards.view')
            <x-sidebar-sublink href="{{ route('report-cards.index') }}" :active="request()->routeIs('report-cards.*')">Report Cards</x-sidebar-sublink>
            @endcan
        </x-sidebar-group>
        @endcanany

        {{-- Finance --}}
        @canany(['fee_types.view', 'invoices.view', 'payments.view', 'finance_reports.view'])
        <x-sidebar-group label="Finance" icon="currency-dollar" :active="request()->routeIs('fee-types.*', 'fee-structures.*', 'invoices.*', 'payments.*')">
            @can('fee_types.view')
            <x-sidebar-sublink href="{{ route('fee-types.index') }}" :active="request()->routeIs('fee-types.*')">Fee Types</x-sidebar-sublink>
            @endcan
            @can('fee_structures.view')
            <x-sidebar-sublink href="{{ route('fee-structures.index') }}" :active="request()->routeIs('fee-structures.*')">Fee Structures</x-sidebar-sublink>
            @endcan
            @can('invoices.view')
            <x-sidebar-sublink href="{{ route('invoices.index') }}" :active="request()->routeIs('invoices.*')">Invoices</x-sidebar-sublink>
            @endcan
            @can('payments.view')
            <x-sidebar-sublink href="{{ route('payments.index') }}" :active="request()->routeIs('payments.*')">Payments</x-sidebar-sublink>
            @endcan
        </x-sidebar-group>
        @endcanany

        {{-- Communication --}}
        @canany(['notices.view', 'messages.view'])
        <x-sidebar-group label="Communication" icon="chat-alt-2" :active="request()->routeIs('notices.*', 'messages.*')">
            @can('notices.view')
            <x-sidebar-sublink href="{{ route('notices.index') }}" :active="request()->routeIs('notices.*')">Notice Board</x-sidebar-sublink>
            @endcan
            @can('messages.view')
            <x-sidebar-sublink href="{{ route('messages.index') }}" :active="request()->routeIs('messages.*')">Messages</x-sidebar-sublink>
            @endcan
        </x-sidebar-group>
        @endcanany

        {{-- Library --}}
        @canany(['books.view', 'book_issues.view'])
        <x-sidebar-group label="Library" icon="library" :active="request()->routeIs('books.*', 'book-issues.*')">
            @can('books.view')
            <x-sidebar-sublink href="{{ route('books.index') }}" :active="request()->routeIs('books.*')">Books</x-sidebar-sublink>
            @endcan
            @can('book_issues.view')
            <x-sidebar-sublink href="{{ route('book-issues.index') }}" :active="request()->routeIs('book-issues.*')">Issue / Return</x-sidebar-sublink>
            @endcan
        </x-sidebar-group>
        @endcanany

        {{-- Parent Portal --}}
        @role('Parent')
        <x-sidebar-group label="My Children" icon="users" :active="request()->routeIs('parent.*')">
            <x-sidebar-sublink href="{{ route('parent.dashboard') }}" :active="request()->routeIs('parent.dashboard')">Dashboard</x-sidebar-sublink>
        </x-sidebar-group>
        @endrole

        {{-- Teacher Portal --}}
        @role('Teacher')
        <x-sidebar-group label="My Portal" icon="academic-cap" :active="request()->routeIs('teacher.*')">
            <x-sidebar-sublink href="{{ route('teacher.dashboard') }}" :active="request()->routeIs('teacher.dashboard')">Dashboard</x-sidebar-sublink>
            <x-sidebar-sublink href="{{ route('teacher.timetable') }}" :active="request()->routeIs('teacher.timetable')">My Timetable</x-sidebar-sublink>
            <x-sidebar-sublink href="{{ route('teacher.attendance') }}" :active="request()->routeIs('teacher.attendance*')">Mark Attendance</x-sidebar-sublink>
            <x-sidebar-sublink href="{{ route('teacher.grades') }}" :active="request()->routeIs('teacher.grades', 'teacher.enter-grades*')">Enter Grades</x-sidebar-sublink>
        </x-sidebar-group>
        @endrole

        {{-- Administration --}}
        @canany(['settings.view', 'users.view', 'roles.view'])
        <div class="pt-4 mt-4 border-t border-gray-200">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider" x-show="sidebarOpen" x-transition>Administration</p>
        </div>
        @can('users.view')
        <x-sidebar-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')" icon="user-group">
            Users
        </x-sidebar-link>
        @endcan
        @can('roles.view')
        <x-sidebar-link href="{{ route('roles.index') }}" :active="request()->routeIs('roles.*')" icon="shield-check">
            Roles & Permissions
        </x-sidebar-link>
        @endcan
        @can('settings.view')
        <x-sidebar-link href="{{ route('demo-requests.index') }}" :active="request()->routeIs('demo-requests.*')" icon="inbox">
            Demo Requests
        </x-sidebar-link>
        <x-sidebar-link href="{{ route('settings.index') }}" :active="request()->routeIs('settings.*')" icon="cog">
            Settings
        </x-sidebar-link>
        @endcan
        @endcanany
    </nav>
</aside>