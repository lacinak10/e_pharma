<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'E-PHARMA - Tableau de Bord Pharmacie')</title>
    <meta name="description" content="@yield('description', 'Tableau de bord pour les pharmacies partenaires E-PHARMA')">

    {{-- Tailwind CDN + config couleurs --}}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    @yield('head')
</head>

<body class="bg-gray-100 font-sans">
<div class="flex h-screen">

    {{-- Sidebar --}}
    <x-admin.sidebar />

    {{-- Main --}}
    <div class="flex flex-col flex-1 overflow-hidden">
        <x-admin.topbar :title="trim($__env->yieldContent('page_title', 'Tableau de bord'))" />

        <main class="flex-1 overflow-y-auto p-4">
            @yield('content')
        </main>
    </div>
</div>

{{-- Modals (stack) --}}
@stack('modals')

<script>
    // Sidebar mobile toggle
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('mobile-sidebar-button');
        const sidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('mobile-sidebar-overlay');

        function openSidebar() {
            if (!sidebar || !overlay) return;
            sidebar.classList.remove('hidden');
            overlay.classList.remove('hidden');
        }

        function closeSidebar() {
            if (!sidebar || !overlay) return;
            sidebar.classList.add('hidden');
            overlay.classList.add('hidden');
        }

        btn?.addEventListener('click', openSidebar);
        overlay?.addEventListener('click', closeSidebar);

        // Modal toggles (generic)
        document.querySelectorAll('[data-modal-open]').forEach(el => {
            el.addEventListener('click', () => {
                const id = el.getAttribute('data-modal-open');
                const modal = document.getElementById(id);
                modal?.classList.remove('hidden');
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(el => {
            el.addEventListener('click', () => {
                const id = el.getAttribute('data-modal-close');
                const modal = document.getElementById(id);
                modal?.classList.add('hidden');
            });
        });

        // Close modal on overlay click
        document.querySelectorAll('[data-modal-overlay]').forEach(overlayEl => {
            overlayEl.addEventListener('click', () => {
                const id = overlayEl.getAttribute('data-modal-overlay');
                const modal = document.getElementById(id);
                modal?.classList.add('hidden');
            });
        });
    });
</script>

@stack('scripts')
</body>
</html>
