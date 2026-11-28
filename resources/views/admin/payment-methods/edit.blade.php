<x-admin-layout title="Edit: {{ $method->title }}">
    <a href="{{ route('admin.payment-methods.index') }}" class="fx-muted text-sm">&larr; All payment details</a>
    <div class="fx-card p-5 mt-4 max-w-3xl">
        @include('admin.payment-methods._form', ['method' => $method, 'action' => route('admin.payment-methods.update', $method), 'submit' => 'Save changes'])
    </div>
</x-admin-layout>
