<x-layout>
    <div class="h-screen flex flex-col items-center justify-center">
        <div class="mb-6">
            <p>Name: {{ auth()->user()->name }}</p>
            <p>Email: {{ auth()->user()->email }}</p>
        </div>
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button class="btn-action">Logout</button>
        </form>
    </div>
</x-layout>