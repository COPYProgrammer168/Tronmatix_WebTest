@extends('dashboard.layout')

@section('title', strtoupper(__('dashboard.nav.feedback')))

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <h1 class="text-2xl font-black text-white mb-6">Customer Feedback</h1>
    <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-900 text-gray-400">
                <tr>
                    <th class="p-4">Name</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Feedback</th>
                    <th class="p-4">Date</th>
                </tr>
            </thead>
            <tbody class="text-white">
                @foreach($feedbacks as $item)
                <tr class="border-t border-gray-700">
                    <td class="p-4">{{ $item->name }}</td>
                    <td class="p-4">{{ $item->email }}</td>
                    <td class="p-4">{{ $item->feedback }}</td>
                    <td class="p-4">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
