## Laravel practice project

**Main steps and terminal commands used to create project (on Linux Ubuntu).**

***This is not meant to be documentation or precise and detailed instructions, it's only to show some of the thought process and make notes for myself.***

Create new project (assuming all basic things are already installed):
```
laravel new laravel-simple-auth
```

If using Zsh and getting:
```
zsh: command not found: laravel
```
This fixes it for me:
```
export PATH="$HOME/.config/composer/vendor/bin:$PATH" 
```

During installation process:
```
Would you like to install a starter kit?
- No starter kit

Which testing framework do you prefer?
- Pest

Which database will your application use?
- MySQL

Default database updated. Would you like to run the default database migrations?
- No
```

Go to `.env` file and if necessary adjust:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_simple_auth
DB_USERNAME=root
DB_PASSWORD=
```
I needed to change `DB_USERNAME` and `DB_PASSWORD`.

Go to project folder and run default database migrations:
```
php artisan migrate
```
During migration process:
```
The database 'laravel_simple_auth' does not exist on the 'mysql' connection.
Would you like to create it?
- Yes
```

Install JavaScript packages and build assets:
```
npm install && npm run build
```

Start server:
```
php artisan serve
```
Project can already be accessed at http://127.0.0.1:8000 or http://localhost:8000/.

Yet I want to access it through https://laravel-simple-auth.dev/ and I know I can make it happen.
Further steps is what I do to achieve it using Nginx.

First, to use `https` locally, I use `mkcert`, I have a folder with local SSL certificates, to create new one I go to that folder and do:
```
mkcert laravel-simple-auth.dev localhost 127.0.0.1 ::1
```

Then create new Nginx server block for `laravel-simple-auth`:
```
sudo nano /etc/nginx/sites-available/laravel-simple-auth
```
Contents of the file:
```
server {
    listen 8085;
    listen 443 ssl;
    ssl_certificate /home/kaspars/Projects/SSL/laravel-simple-auth.dev+3.pem;
    ssl_certificate_key /home/kaspars/Projects/SSL/laravel-simple-auth.dev+3-key.pem;

    server_name localhost laravel-simple-auth laravel-simple-auth.dev;

    root /home/kaspars/Projects/laravel-simple-auth/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Then enable site by creating a symlink to it from the sites-enabled directory:
```
sudo ln -s /etc/nginx/sites-available/laravel-simple-auth /etc/nginx/sites-enabled/
```
Restart Nginx:
```
sudo service nginx restart
```
Then go to `/etc/hosts` and add line:
```
127.0.0.1       laravel-simple-auth laravel-simple-auth.dev
```

And now project can be accessed through https://laravel-simple-auth.dev/.
Another bonus is that now there is also no need to do `php artisan serve` to start the server, can just open https://laravel-simple-auth.dev/ at any time.
Just in case, project can also be accessed through http://localhost:8085/ and http://laravel-simple-auth:8085/.

Next in `/resources/views` create `/components/layout.blade.php` file. Can do:
```
php artisan make:component layout --view
```
In `/components/layout.blade.php` for now can add simple HTML skeleton which can be used for all pages. And include `@yield('main')` or `{{ $slot }}` where each page content will go. I will use `@yield('main')` for now and at this that file will look like this:
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Title</title>
</head>
<body>
    <main>
        @yield('main')
    </main>
</body>
</html>
```

Then change `/resources/views/welcome.blade.php` to something like this:
```
@extends('components.layout')

@section('main')
    <h1>Welcome, Guest!</h1>
@endsection
```

Now, opening https://laravel-simple-auth.dev/, I see "Welcome, Guest!"

Then can change app name in `.env`:
```
APP_NAME="Laravel Simple Auth"
```
And also page title in `layout.blade.php`:
```
<title>{{ env('APP_NAME') }}</title>
```

Also need to include `/resources/css/app.css` and `/resources/js/app.js`, that could be done directly under title:
```
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

Then can also start Vite dev server:
```
npm run dev
```

And to make sure everything is working, I'm adding something simple like this in `/resources/css/app.css`:
```
body {
    background-color: darkcyan;
    color: white;
    text-align: center;
    font-weight: bold;
    font-size: 24px;
    margin: 50px;
}
```

And this in `resources/js/app.js`:
```
console.log('Hello, World!');
```

I see everything is working, so I can also close dev server and just in case test:
```
npm run build
```

To use Tailwind, can follow this guide to set it up:
https://tailwindcss.com/docs/guides/laravel
Although in latest Laravel looks like it's already included.

Then can start dev server again and test Tailwind, also adding custom component class in `/resources/css/app.css`, replacing previously added code with:
```
@layer components {
    .test-container {
        @apply bg-slate-700 m-4 p-4;
    }
}
```
Then `/resources/views/welcome.blade.php` could be adjusted to look like this:
```
@extends('components.layout')

@section('main')
    <div class="test-container">
        <h1 class="text-white font-bold">Welcome, Guest!</h1>
    </div>
@endsection
```

After testing that all setup works properly, create new `auth` folder inside `/resources/views` and two new files inside that new folder:
- `register.blade.php`
- `login.blade.php`

Then add two new routes in `/routes/web.php`
```
Route::view('/register', 'auth.register')->name('register');
Route::view('/login', 'auth.login')->name('login');
```

Then add two buttons in `/resources/views/welcome.blade.php` , while also removing previous test things, can make it look like this:
```
@extends('components.layout')

@section('main')
    <div class="h-screen flex items-center justify-center">
        <a href="{{ route('register') }}" class="btn-action">Register</a>
        <a href="{{ route('login') }}" class="btn-action">Login</a>
    </div>
@endsection
```

And can also make changes in `/resources/css/app.css`, to at this point make it look like this:
```
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer components {
    .btn-action {
        @apply bg-cyan-600 text-white font-bold m-1 py-2 px-6 rounded-md hover:opacity-90 active:opacity-80;
    }
}
```

Now welcome page shows "Register" and "Login" options.

And, before going forwards, I also want to switch from using `@yield('main')` to using `{{ $slot }}` in main layout `/resources/views/components/layout.blade.php`, so I will make it look like this:
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('APP_NAME') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main>
        {{ $slot }}
    </main>
</body>
</html>
```
And `/resources/views/welcome.blade.php` like this:
```
<x-layout>
    <div class="h-screen flex items-center justify-center">
        <a href="{{ route('register') }}" class="btn-action">Register</a>
        <a href="{{ route('login') }}" class="btn-action">Login</a>
    </div>
</x-layout>
```

Next create `AuthController.php`, can do:
```
php artisan make:controller AuthController
```
For now, just add three functions which will simply show that requests are reaching them:
```
public function register(Request $request)
{
    dd($request);
}

public function login(Request $request)
{
    dd($request);
}

public function logout(Request $request)
{
    dd($request);
}
```

Then add new routes in `/routes/web.php`:
```
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
```
Need to also add:
```
use App\Http\Controllers\AuthController;
```

Then in `/resources/views/auth/register.blade.php` create register form, which currently could look like this (remember to include `@csrf`):
```
<x-layout>
    <div class="h-screen flex items-center justify-center">
        <form action="{{ route('register') }}" method="post" class="form-auth">
            @csrf

            <div class="my-4">
                <label for="username">Name:</label>
                <input
                    type="text"
                    name="name"
                    class="input-text"
                >
            </div>

            <div class="my-4">
                <label for="email">Email:</label>
                <input
                    type="text"
                    name="email"
                    class="input-text"
                >
            </div>

            <div class="my-4">
                <label for="password">Password:</label>
                <input
                    type="password"
                    name="password"
                    class="input-text"
                >
            </div>

            <div class="my-4">
                <label for="password_confirmation">Confirm Password:</label>
                <input
                    type="password"
                    name="password_confirmation"
                    class="input-text"
                >
            </div>

            <button class="btn-action w-full m-0 mt-2 mb-6">Register</button>
        </form>
    </div>
</x-layout>
```

In `/resources/views/auth/login.blade.php` create login form, which currently could look like this (remember to include `@csrf`):
```
<x-layout>
    <div class="h-screen flex items-center justify-center">
        <form action="{{ route('login') }}" method="post" class="form-auth">
            @csrf

            <div class="my-4">
                <label for="email">Email:</label>
                <input
                    type="text"
                    name="email"
                    class="input-text"
                >
            </div>

            <div class="my-4">
                <label for="password">Password:</label>
                <input
                    type="password"
                    name="password"
                    class="input-text"
                >
            </div>

            <button class="btn-action w-full m-0 mt-2 mb-6">Login</button>
        </form>
    </div>
</x-layout>
```

In `/resources/css/app.css` also need to add:
```
.form-auth {
    @apply inline-block bg-slate-100 py-2 px-8 rounded-md border border-slate-300;
}

.input-text {
    @apply block min-w-80 p-2 my-2 rounded ring-1 ring-slate-200 focus:outline-none focus:ring-slate-300;
}
```

Now can test both forms by filling them, submitting and inspecting data showed by register and login functions. Can open `request` then `parameters` and check if data from forms have been submitted properly.

Next can create functionality for register function in `/app/Http/Controllers/AuthController.php`, which could look like this:
```
public function register(Request $request)
{
    // Validate
    $fields = $request->validate([
        'name' => 'required|min:5|max:20',
        'email' => 'required|max:255|email|unique:users',
        'password' => 'required|min:5|confirmed',
    ]);

    // Register
    $user = User::create($fields);

    // Login
    Auth::login($user);

    // Redirect
    return redirect('/');
}
```
Need to also include:
```
use App\Models\User;
```
And:
```
use Illuminate\Support\Facades\Auth;
```

Now can try to submit empty form and will see that need to adjust forms to show errors. Every input field which is included in validation, needs to be adjusted to look like this:
```
<div class="my-4">
    <label for="username">Name:</label>
    <input
        type="text"
        name="name"
        class="input-text @error('name') ring-red-500 focus:ring-red-500 @enderror"
    >
    @error('name')
        <p class="text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
```

Additional thing to also keep in mind is that `password_confirmation` field is not being validated separately. In this case validation rules (in register function) for `password` include `confirmed`, which means validation will look for field named `password_confirmation` and just check if it matches `password`. So in this case both password fields in form could look like this:
```
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
```

To keep old values in form fields when there is an error, can add `value` attribute, which, for example, would look like this:
```
value="{{ old('name') }}"
```
Overall `name` field now will look like this:
```
<div class="my-4">
    <label for="username">Name:</label>
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
```
Can add `value` like this to `email` field too and then try to test various error cases.

Then can submit valid form and check database if new user was created properly.

Next can create `home.blade.php` in `/resources/views`, which could look like this:
```
<x-layout>
    <div class="h-screen flex items-center justify-center">
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button class="btn-action">Logout</button>
        </form>
    </div>
</x-layout>
```

And then adjust `/` route in `/routes/web.php` to look like this:
```
Route::get('/', function () {
    if (Auth::check()) {
        return view('home');
    }

    return view('welcome');
});
```

And now I see that https://laravel-simple-auth.dev/ shows me `home` view instead of `welcome` view, because after registering I was also logged in.

To show my info, can adjust `/resources/views/home.blade.php` to look like this:
```
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
```

Now can try to logout and see if request is made properly.

After that can add functionality to logout function, which could look like this:
```
public function logout(Request $request)
{
    // Logout user
    Auth::logout();

    // Invalidate session
    $request->session()->invalidate();

    // Regenerate CSRF token
    $request->session()->regenerateToken();

    // Redirect
    return redirect('/');
}
```

Now can test if it works and then move on and add login functionality, which could look like this:
```
public function login(Request $request)
{
    // Validate
    $fields = $request->validate([
        'email' => 'required|max:255|email',
        'password' => 'required'
    ]);

    // Try to login user
    if (Auth::attempt($fields)) {
        return redirect('/');
    }

    // Failed to login
    return back()->withErrors([
        'failed' => 'The provided credentials do not match our records.'
    ]);
}
```

To display errors, need to also adjust `/resources/views/auth/login.blade.php` the same way as previously did in `/resources/views/auth/register.blade.php`. And additionally add this:
```
@error('failed')
    <p class="text-xs text-red-500">{{ $message }}</p>
@enderror
```

Now can test errors and also valid login credentials, if everything works properly.

Can also add "Remember me" option, by adding this in `/resources/views/auth/login.blade.php`:
```
<div class="my-4">
    <input type="checkbox" name="remember">
    <label for="remember">Remember me</label>
</div>
```

And then passing second parameter to `Auth::attempt` in login function, which would make that part look like this:
```
// Try to login user
if (Auth::attempt($fields, $request->remember)) {
    return redirect('/');
}
```

Now there is still issue that logged in users can access `register` and `login`. To fix that, can adjust routes in `/routes/web.php`, grouping them like this:
```
Route::middleware('guest')->group(function() {
    Route::view('/register', 'auth.register')->name('register');
    Route::view('/login', 'auth.login')->name('login');

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
```
There are also other ways to achieve the same effect, but I will currently leave it like this.

And for this project that's all, just some basic things.