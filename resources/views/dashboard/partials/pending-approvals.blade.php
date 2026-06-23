@props(['approvals' => collect()])

<div class="mi-card">
    <div class="mi-card-head">
        <h2 class="text-sm font-bold text-gray-900">Pending Approvals</h2>
        <a href="{{ route('approvals.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">Inbox</a>
    </div>
    @if ($approvals->isEmpty())
        <div class="db-empty"><i class="fas fa-circle-check mb-2 block text-lg text-green-400"></i>Approval inbox is clear.</div>
    @else
        <div class="db-table-wrap">
            <table class="db-table">
                <thead>
                    <tr>
                        <th style="width:55%">Request</th>
                        <th>Module</th>
                        <th>From</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($approvals as $approval)
                        <tr>
                            <td>
                                <a href="{{ route('approvals.show', $approval) }}" title="{{ $approval->documentReference() }}">
                                    {{ $approval->documentReference() }}
                                </a>
                            </td>
                            <td class="text-gray-500 text-xs">{{ $approval->moduleLabel() }}</td>
                            <td title="{{ $approval->requester?->name }}">{{ $approval->requester?->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
