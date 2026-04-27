<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'E-PHARMA - Admin')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('/assets/images/logo.jpeg') }}">
    <meta name="description" content="@yield('description', 'Espace Admin E-PHARMA')">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#3b82f6",
                        secondary: "#1e40af",
                        accent: "#10b981",
                        danger: "#ef4444"
                    }
                }
            }
        }
    </script>

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Alpine (dropdown, etc.) --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @yield('head')
</head>

<body class="bg-gray-100 font-sans">
<div class="flex h-screen">

    {{-- Sidebar (role-aware) --}}
    <x-admin.sidebar />

    {{-- Main --}}
    <div class="flex flex-col flex-1 overflow-hidden">
        <x-admin.topbar :title="trim($__env->yieldContent('page_title', 'Tableau de bord'))" />

        <main class="flex-1 overflow-y-auto p-4">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 flex items-start gap-3">
                    <i class="fa-regular fa-circle-check mt-0.5"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

@stack('modals')

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('mobile-sidebar-button');
        const sidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('mobile-sidebar-overlay');

        const open = () => { sidebar?.classList.remove('hidden'); overlay?.classList.remove('hidden'); };
        const close = () => { sidebar?.classList.add('hidden'); overlay?.classList.add('hidden'); };

        btn?.addEventListener('click', open);
        overlay?.addEventListener('click', close);

        document.querySelectorAll('[data-modal-open]').forEach(el => {
            el.addEventListener('click', () => {
                const id = el.getAttribute('data-modal-open');
                document.getElementById(id)?.classList.remove('hidden');
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(el => {
            el.addEventListener('click', () => {
                const id = el.getAttribute('data-modal-close');
                document.getElementById(id)?.classList.add('hidden');
            });
        });

        document.querySelectorAll('[data-modal-overlay]').forEach(el => {
            el.addEventListener('click', () => {
                const id = el.getAttribute('data-modal-overlay');
                document.getElementById(id)?.classList.add('hidden');
            });
        });
    });
</script>

@stack('scripts')
</body>
</html>
