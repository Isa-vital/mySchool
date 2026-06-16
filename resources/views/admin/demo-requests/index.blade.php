<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Demo Requests</h2>
            <span class="px-3 py-1 text-sm font-medium rounded-full text-white" style="background: var(--primary-color);">
                {{ $demoRequests->total() }} total
            </span>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">School</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">WhatsApp</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($demoRequests as $request)
                    <tr class="{{ $request->contacted_at ? 'bg-green-50/40' : '' }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $request->school_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $request->contact_name }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $request->whatsapp) }}" target="_blank" rel="noopener noreferrer" class="font-medium hover:underline" style="color: var(--primary-color);">
                                {{ $request->whatsapp }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $request->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($request->contacted_at)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Contacted</span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">New</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-right">
                            <div class="flex items-center justify-end space-x-3">
                                @can('settings.edit')
                                <form method="POST" action="{{ route('demo-requests.contacted', $request) }}">
                                    @csrf
                                    <button type="submit" class="font-medium hover:underline" style="color: var(--primary-color);">
                                        {{ $request->contacted_at ? 'Mark new' : 'Mark contacted' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('demo-requests.destroy', $request) }}" onsubmit="return confirm('Delete this demo request?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">No demo requests yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($demoRequests->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $demoRequests->links() }}
        </div>
        @endif
    </div>
</x-app-layout>