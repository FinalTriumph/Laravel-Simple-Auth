<x-layout>
    <div class="h-screen flex items-center justify-center">
        <form action="{{ route('register') }}" method="post" class="form-auth">
            @csrf

            <div class="my-4">
                <label for="name">Name:</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="input-text @error('name') ring-red-500 focus:ring-red-500 @enderror"
                >
                @error('name')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="my-4">
                <label for="email">Email:</label>
                <input
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    class="input-text @error('email') ring-red-500 focus:ring-red-500 @enderror"
                >
                @error('email')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="my-4">
                <label for="password">Password:</label>
                <input
                    type="password"
                    name="password"
                    class="input-text @error('password') ring-red-500 focus:ring-red-500 @enderror"
                >
                @error('password')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="my-4">
                <label for="password_confirmation">Confirm Password:</label>
                <input
                    type="password"
                    name="password_confirmation"
                    class="input-text @error('password') ring-red-500 focus:ring-red-500 @enderror"
                >
            </div>

            <button class="btn-action w-full m-0 mt-2 mb-6">Register</button>
        </form>
    </div>
</x-layout>