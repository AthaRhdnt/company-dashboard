<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://alpinejs.dev/plugins/collapse "></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Load Alpine.js and Collapse plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Define a custom color palette for the primary color */
        :root {
            --color-primary: 99, 102, 241; /* indigo-600 */
        }
        .text-primary-600 { color: rgb(var(--color-primary)); }
        /* Ensuring the body and HTML take full height for the sidebar demo */
        html, body {
            height: 100%;
        }
    </style>
</head>
<!-- START: layouts.app.blade.php (Main Layout Wrapper) -->
<body class="font-sans antialiased bg-gray-100 h-full">

    <!-- Main Wrapper using Alpine.js for state management -->
    <!-- This div holds the entire layout and global state -->
    <div x-data="{ sidebarOpen: true, activePage: 'dashboard', activeDropdown: 'none' }" class="flex h-screen overflow-hidden">

            @include('layouts.sidebar')
            <main class="flex-1 p-8 overflow-y-auto">
                {{$slot}}
            </main>
        </div>
        <script>
            // Disable Enter to submit on all forms
            document.addEventListener('keydown', function (e) {
                // If Enter key AND target is inside a form AND not a textarea
                if (e.key === 'Enter' && e.target.form && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    return false;
                }
            });
        </script>
    </body>
</html>