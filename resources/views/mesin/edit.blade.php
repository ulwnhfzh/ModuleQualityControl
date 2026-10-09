@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-sm mb-6 max-w-4xl mx-auto">
    <h2 class="text-xl font-bold mb-4">Edit Data Mesin</h2>

    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <p class="font-bold mb-1">Ada kesalahan:</p>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('mesin.update', $mesin->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-semibold mb-1">Tool / Machine Name *</label>
                <input type="text" name="tool_name" value="{{ old('tool_name', $mesin->tool_name) }}" required
                       class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Machine Model</label>
                <input type="text" name="machine_model" value="{{ old('machine_model', $mesin->machine_model) }}"
                       class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Code Number</label>
                <input type="text" name="code_number" value="{{ old('code_number', $mesin->code_number) }}"
                       class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Serial Number</label>
                <input type="text" name="serial_number" value="{{ old('serial_number', $mesin->serial_number) }}"
                       class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Maker</label>
                <input type="text" name="maker" value="{{ old('maker', $mesin->maker) }}"
                       class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">No. Machine</label>
                <input type="text" name="no_machine" value="{{ old('no_machine', $mesin->no_machine) }}"
                       class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Retension</label>
                <input type="text" name="retension" value="{{ old('retension', $mesin->retension) }}"
                       class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ old('start_date', $mesin->start_date) }}"
                       class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Next Date</label>
                <input type="date" name="next_date" value="{{ old('next_date', $mesin->next_date) }}"
                       class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-semibold hover:bg-blue-700 transition">Update</button>
            <a href="{{ route('mesin.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection