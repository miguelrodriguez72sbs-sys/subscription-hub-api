@verbatim
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', 'Circular', system-ui, -apple-system, sans-serif; background: #121212; }
        .scroll-thin::-webkit-scrollbar { width: 8px; height: 8px; }
        .scroll-thin::-webkit-scrollbar-thumb { background: #282828; border-radius: 999px; }
        .scroll-thin::-webkit-scrollbar-track { background: transparent; }
        .status-active, .status-paid, .status-succeeded, .status-admin { background: rgba(29, 185, 84, .15); color: #1ed760; }
        .status-pending { background: rgba(251, 191, 36, .15); color: #fbbf24; }
        .status-cancelled, .status-client { background: rgba(113, 113, 122, .2); color: #a1a1aa; }
        .status-expired, .status-failed { background: rgba(239, 68, 68, .15); color: #f87171; }
        input:-webkit-autofill { -webkit-box-shadow: 0 0 0 1000px #121212 inset; -webkit-text-fill-color: #fff; }
    </style>
</head>
<body class="bg-[#121212] text-white min-h-screen">

    <!-- AUTH VIEW -->
    <div id="auth-view" class="hidden min-h-screen items-center justify-center p-4 relative overflow-hidden">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#1db954]/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-emerald-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative w-full max-w-md bg-[#181818] border border-white/5 rounded-2xl shadow-2xl p-8">
            <div class="flex items-center justify-center gap-2.5 mb-2">
                <svg viewBox="0 0 32 32" class="w-10 h-10">
                    <rect width="32" height="32" rx="8" fill="#1db954"/>
                    <path d="M16 8v9.3a3 3 0 1 0 1.5-2.6V9.5h3V8h-4.5z" fill="black"/>
                </svg>
                <h1 class="text-2xl font-bold tracking-tight">Subscription Hub</h1>
            </div>
            <p class="text-center text-sm text-zinc-400 mb-7">Gestion de suscripciones y cobros recurrentes</p>

            <div class="flex mb-6 bg-[#121212] rounded-full p-1">
                <button id="tab-login" onclick="showAuthForm('login')" class="flex-1 py-2 rounded-full font-semibold text-zinc-400 auth-tab">Iniciar sesion</button>
                <button id="tab-register" onclick="showAuthForm('register')" class="flex-1 py-2 rounded-full font-semibold text-zinc-400 auth-tab">Registrarse</button>
            </div>

            <form id="login-form" class="space-y-4" onsubmit="doLogin(event)">
                <input id="login-email" type="email" placeholder="Correo electronico" class="w-full bg-[#121212] border border-white/10 rounded-lg px-4 py-2.5 text-sm placeholder-zinc-500 focus:outline-none focus:border-[#1db954] focus:ring-1 focus:ring-[#1db954]" required>
                <input id="login-password" type="password" placeholder="Contrasena" class="w-full bg-[#121212] border border-white/10 rounded-lg px-4 py-2.5 text-sm placeholder-zinc-500 focus:outline-none focus:border-[#1db954] focus:ring-1 focus:ring-[#1db954]" required>
                <button class="w-full bg-[#1db954] hover:bg-[#1ed760] text-black font-bold rounded-full py-2.5 transition">Entrar</button>
            </form>

            <form id="register-form" class="hidden space-y-4" onsubmit="doRegister(event)">
                <input id="reg-name" type="text" placeholder="Nombre completo" class="w-full bg-[#121212] border border-white/10 rounded-lg px-4 py-2.5 text-sm placeholder-zinc-500 focus:outline-none focus:border-[#1db954] focus:ring-1 focus:ring-[#1db954]" required>
                <input id="reg-email" type="email" placeholder="Correo electronico" class="w-full bg-[#121212] border border-white/10 rounded-lg px-4 py-2.5 text-sm placeholder-zinc-500 focus:outline-none focus:border-[#1db954] focus:ring-1 focus:ring-[#1db954]" required>
                <input id="reg-password" type="password" placeholder="Contrasena (minimo 8)" class="w-full bg-[#121212] border border-white/10 rounded-lg px-4 py-2.5 text-sm placeholder-zinc-500 focus:outline-none focus:border-[#1db954] focus:ring-1 focus:ring-[#1db954]" required minlength="8">
                <input id="reg-password-confirm" type="password" placeholder="Confirmar contrasena" class="w-full bg-[#121212] border border-white/10 rounded-lg px-4 py-2.5 text-sm placeholder-zinc-500 focus:outline-none focus:border-[#1db954] focus:ring-1 focus:ring-[#1db954]" required minlength="8">
                <button class="w-full bg-[#1db954] hover:bg-[#1ed760] text-black font-bold rounded-full py-2.5 transition">Crear cuenta</button>
            </form>

            <p id="auth-error" class="hidden mt-4 text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg p-3"></p>
            <p class="mt-6 text-xs text-center text-zinc-500">
                Demo: admin@example.com / password (admin) &middot; cliente@example.com / password (cliente)
            </p>
        </div>
    </div>

    <!-- APP VIEW -->
    <div id="app-view" class="hidden">
        <div class="flex h-screen overflow-hidden">

            <!-- SIDEBAR -->
            <div id="sidebar-overlay" onclick="toggleSidebar(false)" class="fixed inset-0 z-30 bg-black/70 md:hidden hidden"></div>
            <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col bg-black -translate-x-full md:static md:translate-x-0 transition-transform duration-300 shrink-0">
                <div class="px-6 pt-6 pb-4 flex items-center gap-2.5">
                    <svg viewBox="0 0 32 32" class="w-9 h-9">
                        <rect width="32" height="32" rx="8" fill="#1db954"/>
                        <path d="M16 8v9.3a3 3 0 1 0 1.5-2.6V9.5h3V8h-4.5z" fill="black"/>
                    </svg>
                    <span class="font-bold text-lg tracking-tight">Subscription Hub</span>
                </div>

                <nav class="flex-1 px-3 space-y-1 mt-2">
                    <button data-tab="dashboard" onclick="go('dashboard')" class="nav-tab w-full flex items-center gap-4 px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-400 hover:text-white transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                        Dashboard
                    </button>
                    <button data-tab="plans" onclick="go('plans')" class="nav-tab w-full flex items-center gap-4 px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-400 hover:text-white transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z"/></svg>
                        Planes
                    </button>
                    <button data-tab="subscriptions" onclick="go('subscriptions')" class="nav-tab w-full flex items-center gap-4 px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-400 hover:text-white transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Suscripciones
                    </button>
                    <button data-tab="invoices" onclick="go('invoices')" class="nav-tab w-full flex items-center gap-4 px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-400 hover:text-white transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        Facturas
                    </button>
                    <button data-tab="payments" onclick="go('payments')" class="nav-tab w-full flex items-center gap-4 px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-400 hover:text-white transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                        Pagos
                    </button>
                    <button data-tab="users" id="tab-users" onclick="go('users')" class="nav-tab w-full hidden items-center gap-4 px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-400 hover:text-white transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        Usuarios
                    </button>
                    <button data-tab="reports" id="tab-reports" onclick="go('reports')" class="nav-tab w-full hidden items-center gap-4 px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-400 hover:text-white transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        Reportes
                    </button>
                </nav>

                <div class="p-3 border-t border-white/5 mt-4">
                    <div class="flex items-center gap-3 px-3 py-2">
                        <div class="w-9 h-9 shrink-0 rounded-full bg-gradient-to-br from-[#1db954] to-emerald-800 flex items-center justify-center font-bold text-black text-sm">
                            <span id="user-avatar">?</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p id="user-info" class="text-sm font-semibold truncate"></p>
                            <p id="user-role" class="text-xs text-zinc-500"></p>
                        </div>
                        <button onclick="logout()" title="Salir" class="text-zinc-500 hover:text-white transition shrink-0">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                        </button>
                    </div>
                    <p class="px-3 pt-2 text-[11px] text-zinc-600">Pasarela de pago simulada</p>
                </div>
            </aside>

            <!-- MAIN -->
            <div class="flex-1 flex flex-col min-w-0">
                <header class="sticky top-0 z-10 bg-[#121212]/95 backdrop-blur border-b border-white/5 px-4 md:px-6 py-4 flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="md:hidden text-zinc-400 hover:text-white transition shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>
                    <div class="min-w-0">
                        <h2 id="page-title" class="text-xl md:text-2xl font-bold tracking-tight truncate">Dashboard</h2>
                        <p id="page-sub" class="text-sm text-zinc-400 truncate"></p>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto scroll-thin p-4 md:p-6">
                    <div id="alert" class="hidden mb-4 rounded-xl p-3.5 text-sm border"></div>
                    <section id="tab-dashboard" class="tab-content"></section>
                    <section id="tab-plans" class="tab-content hidden"></section>
                    <section id="tab-subscriptions" class="tab-content hidden"></section>
                    <section id="tab-invoices" class="tab-content hidden"></section>
                    <section id="tab-payments" class="tab-content hidden"></section>
                    <section id="tab-users" class="tab-content hidden"></section>
                    <section id="tab-reports" class="tab-content hidden"></section>
                </main>
            </div>
        </div>
    </div>

<script>
const API = '/api';
let token = localStorage.getItem('sh_token') || null;
let user = null;

const $ = (id) => document.getElementById(id);

const I = {
    play: '<svg viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 ml-0.5"><path d="M8 5v14l11-7z"/></svg>',
    note: '<svg viewBox="0 0 24 24" fill="currentColor" class="w-14 h-14 text-white/60"><path d="M12 3v10.55A4 4 0 1 0 14 17V7h4V3h-6z"/></svg>',
    chart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>',
    pdf: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>',
};

function fmt(n) { return '$' + Number(n).toFixed(2); }
function dt(s) { return s ? String(s).slice(0, 10) : '-'; }
function esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function badge(status) { return `<span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold status-${esc(status)}">${esc(status)}</span>`; }

function showAlert(msg, ok = true) {
    const a = $('alert');
    a.classList.remove('hidden');
    a.className = 'mb-4 rounded-xl p-3.5 text-sm border ' + (ok ? 'bg-[#1db954]/10 border-[#1db954]/25 text-[#1ed760]' : 'bg-red-500/10 border-red-500/25 text-red-400');
    a.textContent = msg;
    setTimeout(() => a.classList.add('hidden'), 5000);
}

async function api(path, { method = 'GET', body } = {}) {
    const headers = { 'Accept': 'application/json' };
    let payload;
    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify(body);
    }
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const res = await fetch(API + path, { method, headers, body: payload });
    const data = await res.json().catch(() => ({}));

    if (res.status === 401) {
        clearSession();
        throw new Error('Tu sesion expiro. Inicia sesion de nuevo.');
    }
    if (!res.ok) {
        const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Error del servidor');
        throw new Error(msg);
    }
    return data;
}

/* ---------- AUTH ---------- */
function showAuthForm(which) {
    $('login-form').classList.toggle('hidden', which !== 'login');
    $('register-form').classList.toggle('hidden', which !== 'register');
    document.querySelectorAll('.auth-tab').forEach(t => {
        const active = t.id === 'tab-' + which;
        t.classList.toggle('bg-[#1db954]', active);
        t.classList.toggle('text-black', active);
        t.classList.toggle('font-bold', active);
        t.classList.toggle('text-zinc-400', !active);
    });
    $('auth-error').classList.add('hidden');
}

async function doLogin(e) {
    e.preventDefault();
    try {
        const data = await api('/login', { method: 'POST', body: { email: $('login-email').value, password: $('login-password').value } });
        token = data.token;
        localStorage.setItem('sh_token', token);
        await init();
    } catch (err) { showAuthError(err.message); }
}

async function doRegister(e) {
    e.preventDefault();
    try {
        const data = await api('/register', { method: 'POST', body: {
            name: $('reg-name').value,
            email: $('reg-email').value,
            password: $('reg-password').value,
            password_confirmation: $('reg-password-confirm').value,
        }});
        token = data.token;
        localStorage.setItem('sh_token', token);
        await init();
    } catch (err) { showAuthError(err.message); }
}

function showAuthError(msg) {
    const el = $('auth-error');
    el.textContent = msg;
    el.classList.remove('hidden');
}

function clearSession() {
    token = null;
    user = null;
    localStorage.removeItem('sh_token');
}

function logout() {
    if (token) api('/logout', { method: 'POST' }).catch(() => {});
    clearSession();
    $('app-view').classList.add('hidden');
    $('auth-view').classList.remove('hidden');
    $('auth-view').classList.add('flex');
    $('sidebar-overlay').classList.add('hidden');
    showAuthForm('login');
}

/* ---------- LAYOUT / NAV ---------- */
function toggleSidebar(force) {
    const sb = $('sidebar');
    const hide = force !== undefined ? force : !sb.classList.contains('-translate-x-full');
    sb.classList.toggle('-translate-x-full', hide);
    $('sidebar-overlay').classList.toggle('hidden', hide);
}

const META = {
    dashboard: ['Dashboard', 'Resumen general de la plataforma'],
    plans: ['Planes', 'Tus aplicaciones de streaming'],
    subscriptions: ['Suscripciones', 'Historial de suscripciones'],
    invoices: ['Facturas', 'Historial de facturas y cobros'],
    payments: ['Pagos', 'Historial de pagos procesados'],
    users: ['Usuarios', 'Gestion de usuarios y roles'],
    reports: ['Reportes', 'Metricas del periodo'],
};

function go(tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
    document.querySelectorAll('.nav-tab').forEach(t => {
        const active = t.dataset.tab === tab;
        t.classList.toggle('bg-[#282828]', active);
        t.classList.toggle('text-white', active);
        t.classList.toggle('text-zinc-400', !active);
    });
    const target = $('tab-' + tab);
    target.classList.remove('hidden');
    const meta = META[tab];
    $('page-title').textContent = meta[0];
    $('page-sub').textContent = user && user.role === 'admin' && tab === 'dashboard' ? 'Resumen general de la plataforma' : meta[1];
    toggleSidebar(false);
    const render = { dashboard: renderDashboard, plans: renderPlans, subscriptions: renderSubscriptions, invoices: renderInvoices, payments: renderPayments, users: renderUsers, reports: renderReports };
    render[tab]();
}

/* ---------- DASHBOARD ---------- */
async function renderDashboard() {
    const box = $('tab-dashboard');
    box.innerHTML = '<p class="text-zinc-500">Cargando...</p>';
    try {
        const d = await api('/dashboard');
        if (user.role === 'admin') {
            box.innerHTML = `
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    ${card('Clientes', d.total_customers)}
                    ${card('Planes', d.total_plans)}
                    ${card('Suscripciones activas', d.active_subscriptions)}
                    ${card('Renovaciones 7 dias', d.upcoming_renewals)}
                    ${card('Ingresos totales', fmt(d.total_revenue))}
                    ${card('Ingresos 30 dias', fmt(d.revenue_last_30_days))}
                    ${card('Pendientes', d.pending_subscriptions)}
                    ${card('Canceladas', d.cancelled_subscriptions)}
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    ${listBlock('Ultimas suscripciones', d.recent_subscriptions?.data ?? [], subRow)}
                    ${listBlock('Ultimas facturas', d.recent_invoices?.data ?? [], invRow)}
                </div>`;
        } else {
            const s = d.active_subscription;
            box.innerHTML = `
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    ${card('Suscripcion activa', s ? 'Si' : 'Ninguna')}
                    ${card('Total suscripciones', d.total_subscriptions)}
                    ${card('Proxima facturacion', dt(d.next_billing_date))}
                    ${card('Total pagado', fmt(d.paid_amount))}
                </div>
                ${s ? `
                <div class="bg-gradient-to-r from-[#1db954]/15 via-[#121212] to-[#121212] border border-[#1db954]/25 rounded-2xl p-6 flex items-center gap-5">
                    <div class="w-20 h-20 shrink-0 rounded-xl bg-gradient-to-br from-[#1db954] to-emerald-900 flex items-center justify-center shadow-lg shadow-[#1db954]/20">
                        ${I.play}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs uppercase tracking-widest text-[#1db954] font-bold mb-1">Plan activo</p>
                        <h3 class="text-xl md:text-2xl font-bold truncate">${planLabel(s)}</h3>
                        <p class="text-sm text-zinc-400 mt-1">Inicio ${dt(s.starts_at)} &middot; Fin ${dt(s.ends_at)} &middot; Proxima facturacion ${dt(s.next_billing_date)}</p>
                    </div>
                    ${badge(s.status)}
                </div>`
                : `<div class="bg-[#181818] border border-white/5 rounded-xl p-8 text-center">
                    <p class="text-zinc-400 mb-4">No tienes una suscripcion activa.</p>
                    <button onclick="go('plans')" class="bg-[#1db954] hover:bg-[#1ed760] text-black font-bold px-6 py-2.5 rounded-full text-sm transition">Ver planes</button>
                </div>`}`;
        }
    } catch (err) { box.innerHTML = '<p class="text-red-400">' + esc(err.message) + '</p>'; }
}

function card(label, value) {
    return `<div class="bg-[#181818] hover:bg-[#1f1f1f] border border-white/5 rounded-xl p-5 transition">
        <p class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">${esc(label)}</p>
        <p class="text-2xl font-bold mt-1.5">${value}</p>
    </div>`;
}
function listBlock(title, items, rowFn) {
    const head = `<div class="flex items-center gap-2 px-5 py-4 border-b border-white/5">
        <span class="text-[#1db954]">${I.chart}</span>
        <h3 class="font-bold">${esc(title)}</h3>
    </div>`;
    if (!items.length) return `<div class="bg-[#181818] border border-white/5 rounded-xl">${head}<p class="text-sm text-zinc-500 px-5 py-6">Sin registros.</p></div>`;
    return `<div class="bg-[#181818] border border-white/5 rounded-xl overflow-hidden">${head}<div class="overflow-x-auto"><table class="w-full text-sm">${items.map(rowFn).join('')}</table></div></div>`;
}
function subRow(s) {
    return `<tr class="border-b border-white/5 last:border-0 hover:bg-white/[0.03]">
        <td class="px-5 py-3 text-zinc-400">#${s.id}</td><td class="px-5 py-3 font-semibold">${planLabel(s)}</td>
        <td class="px-5 py-3">${badge(s.status)}</td><td class="px-5 py-3 text-zinc-400">${dt(s.next_billing_date)}</td>
    </tr>`;
}
function invRow(i) {
    return `<tr class="border-b border-white/5 last:border-0 hover:bg-white/[0.03]">
        <td class="px-5 py-3 text-zinc-400">#${i.id}</td><td class="px-5 py-3 font-semibold">${planLabel(i)}</td>
        <td class="px-5 py-3 font-bold">${fmt(i.amount)}</td><td class="px-5 py-3">${badge(i.status)}</td>
    </tr>`;
}

/* ---------- PLANS ---------- */
const APPS = {
    Netflix: { gradient: 'from-red-600 via-red-800 to-black', glyph: 'N' },
    Spotify: { gradient: 'from-green-500 via-green-800 to-black', glyph: 'S' },
    YouTube: { gradient: 'from-red-500 via-red-800 to-black', glyph: 'Y' },
    Amazon: { gradient: 'from-amber-500 via-orange-800 to-black', glyph: 'A' },
    Disney: { gradient: 'from-blue-500 via-indigo-800 to-black', glyph: 'D' },
};
const DEFAULT_APP = { gradient: 'from-zinc-600 via-zinc-800 to-black', glyph: '?' };
function appMeta(app) { return APPS[app] || DEFAULT_APP; }
function appBadge(app) {
    if (!app) return '<span class="text-zinc-500 text-xs">-</span>';
    const meta = appMeta(app);
    return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-white/5 border border-white/10 text-zinc-200">
        <span class="w-2 h-2 rounded-full bg-gradient-to-br ${meta.gradient}"></span>${esc(app)}
    </span>`;
}
function planLabel(p) {
    return p.application ? esc(p.application) + ' &middot; ' + esc(p.plan) : esc(p.plan);
}

let plansApp = null;
let plansCache = [];

async function renderPlans() {
    const box = $('tab-plans');
    box.innerHTML = '<p class="text-zinc-500">Cargando...</p>';
    try {
        const plans = await api('/membership-plans');
        plansCache = plans.data ?? plans;
        localStorage.setItem('sh_plans_cache', JSON.stringify(plansCache));
        const admin = user.role === 'admin';

        if (plansApp === null) {
            let subs = [];
            if (!admin) {
                const s = await api('/subscriptions');
                subs = s.data ?? s;
            }
            const apps = [...new Set(plansCache.map(p => p.application || 'Otros').filter(Boolean))];
            box.innerHTML = `
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <p class="text-zinc-400 text-sm">Aplicaciones de streaming y sus planes</p>
                    ${admin ? `<button onclick="newPlanForm('Otros')" class="bg-white text-black hover:bg-white/80 px-5 py-2.5 rounded-full text-sm font-bold transition">+ Nuevo plan</button>` : ''}
                </div>
                <div id="plan-form-wrap"></div>
                ${apps.length
                    ? `<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">${apps.map(a => appCard(a, plansCache.filter(p => (p.application || 'Otros') === a), admin, subs)).join('')}</div>`
                    : '<div class="bg-[#181818] border border-white/5 rounded-xl p-8 text-center text-zinc-400">No hay planes disponibles.</div>'}
            `;
        } else {
            const appPlans = plansCache.filter(p => (p.application || 'Otros') === plansApp);
            box.innerHTML = await renderAppPlans(plansApp, appPlans, admin);
        }
    } catch (err) { box.innerHTML = '<p class="text-red-400">' + esc(err.message) + '</p>'; }
}

function appCard(app, plans, admin, subs) {
    const meta = appMeta(app);
    const active = !admin && subs.some(s => s.application === app && s.status === 'active');
    return `
    <button onclick="openApp('${esc(app)}')" class="group text-left bg-[#181818] hover:bg-[#282828] border border-white/5 rounded-xl overflow-hidden transition-all duration-300 hover:-translate-y-0.5">
        <div class="relative h-36 bg-gradient-to-br ${meta.gradient} flex items-center justify-center">
            <div class="w-16 h-16 rounded-2xl bg-black/30 border border-white/10 flex items-center justify-center text-3xl font-black text-white shadow-lg">${esc(meta.glyph)}</div>
            ${active ? `<span class="absolute top-3 right-3 inline-block w-2.5 h-2.5 rounded-full bg-[#1db954] shadow-[0_0_10px_rgba(29,185,84,.8)]" title="Suscripcion activa"></span>` : ''}
            <span class="absolute bottom-3 left-3 text-white/80 text-[11px] font-bold uppercase tracking-widest">${plans.length} planes</span>
        </div>
        <div class="p-4">
            <h3 class="font-bold text-lg">${esc(app)}</h3>
            <p class="text-sm text-zinc-400 mt-0.5">${admin ? 'Gestionar planes' : active ? 'Suscripcion activa' : 'Explorar planes'}</p>
            <span class="mt-3 inline-flex items-center gap-1.5 text-[#1db954] text-sm font-semibold">
                ${admin ? 'Gestionar' : 'Ver planes'}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 transition-transform group-hover:translate-x-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </span>
        </div>
    </button>`;
}

function openApp(app) {
    plansApp = app || null;
    renderPlans();
}

async function renderAppPlans(app, plans, admin) {
    const meta = appMeta(app);
    let subs = [];
    let active = null;
    if (!admin) {
        const s = await api('/subscriptions');
        subs = s.data ?? s;
        active = subs.find(x => x.application === app && x.status === 'active');
    }
    return `
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <button onclick="openApp(null)" class="bg-[#181818] hover:bg-[#282828] border border-white/5 rounded-full p-2 transition" title="Volver">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </button>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br ${meta.gradient} flex items-center justify-center text-xl font-black text-white">${esc(meta.glyph)}</div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold">${esc(app)}</h2>
                <p class="text-sm text-zinc-400">Elige el plan que se adapta a ti</p>
            </div>
            ${active ? `<span class="ml-auto">${badge('active')}</span>` : ''}
        </div>
        ${!admin && active ? `
        <div class="bg-[#1db954]/10 border border-[#1db954]/25 rounded-xl px-5 py-4 mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-[#1ed760]">Suscripcion activa</p>
                <p class="text-sm text-zinc-300 mt-0.5">Plan ${esc(active.plan)} &middot; Proxima facturacion ${dt(active.next_billing_date)}</p>
            </div>
            <button onclick="cancelPlanSub(${active.id})" class="shrink-0 border border-red-500/40 text-red-400 hover:bg-red-500/10 rounded-full px-4 py-1.5 text-xs font-semibold transition">Cancelar</button>
        </div>` : ''}
        ${admin ? `<div class="mb-6"><button onclick="newPlanForm('${esc(app)}')" class="bg-white text-black hover:bg-white/80 px-5 py-2.5 rounded-full text-sm font-bold transition">+ Nuevo plan</button></div>` : ''}
        <div id="plan-form-wrap"></div>
        ${plans.length
            ? `<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">${plans.map(p => planCard(p, admin)).join('')}</div>`
            : '<div class="bg-[#181818] border border-white/5 rounded-xl p-8 text-center text-zinc-400">Sin planes para esta aplicacion.</div>'}
    `;
}

function planCard(p, admin) {
    const meta = appMeta(p.application || 'Otros');
    return `
    <div class="group bg-[#181818] hover:bg-[#282828] border border-white/5 rounded-xl p-4 transition-all duration-300 hover:-translate-y-0.5">
        <div class="relative mb-4">
            <div class="h-28 rounded-lg bg-gradient-to-br ${meta.gradient} flex items-center justify-center overflow-hidden">
                <div class="w-12 h-12 rounded-xl bg-black/30 border border-white/10 flex items-center justify-center text-xl font-black text-white">${esc(meta.glyph)}</div>
            </div>
            ${!admin ? `
            <button onclick="subscribe(${p.id})" class="absolute bottom-2 right-2 w-12 h-12 rounded-full bg-[#1db954] text-black flex items-center justify-center shadow-lg shadow-black/40 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 hover:scale-105 hover:bg-[#1ed760]" title="Suscribirse">
                ${I.play}
            </button>` : ''}
        </div>
        <h3 class="font-bold truncate">${esc(p.name)}</h3>
        <p class="text-sm text-zinc-400 line-clamp-2 mt-1 min-h-10">${esc(p.description || 'Sin descripcion')}</p>
        <div class="flex items-center justify-between mt-3">
            <p class="text-[#1db954] font-bold text-lg">${fmt(p.price)} <span class="text-xs font-normal text-zinc-500">/ ${p.duration_days} dias</span></p>
            ${badge(p.status === 1 || p.status === true ? 'active' : 'cancelled')}
        </div>
        ${admin ? `<div class="flex gap-2 mt-4">
            <button onclick="editPlanForm(${p.id})" class="flex-1 border border-white/15 hover:border-white/40 rounded-full py-1.5 text-xs font-semibold transition">Editar</button>
            <button onclick="deletePlan(${p.id})" class="flex-1 bg-red-500/15 text-red-400 hover:bg-red-500/25 rounded-full py-1.5 text-xs font-semibold transition">Eliminar</button>
        </div>` : ''}
    </div>`;
}

function newPlanForm(app) {
    $('plan-form-wrap').innerHTML = planFormHtml({ application: app || '' });
}
function editPlanForm(id) {
    const p = (JSON.parse(localStorage.getItem('sh_plans_cache') || '[]')).find(x => x.id === id) || {};
    $('plan-form-wrap').innerHTML = planFormHtml(p);
}
function planFormHtml(p = {}) {
    const inp = 'w-full bg-[#121212] border border-white/10 rounded-lg px-3 py-2 text-sm placeholder-zinc-500 focus:outline-none focus:border-[#1db954]';
    const appList = [...new Set(['Netflix', 'Spotify', 'YouTube', 'Amazon', 'Disney', 'Otros', ...plansCache.map(x => x.application || 'Otros')])];
    return `<div class="bg-[#181818] border border-white/5 rounded-xl p-5 mb-6">
        <h3 class="font-bold mb-4">${p.id ? 'Editar plan' : 'Nuevo plan'}</h3>
        <div class="grid md:grid-cols-2 gap-3">
            <input id="pf-name" placeholder="Nombre del plan" value="${esc(p.name || '')}" class="${inp}">
            <select id="pf-app" class="${inp}">
                ${appList.map(a => `<option value="${esc(a)}" ${(p.application || 'Otros') === a ? 'selected' : ''}>${esc(a)}</option>`).join('')}
            </select>
            <input id="pf-price" type="number" step="0.01" min="0" placeholder="Precio" value="${p.price ?? ''}" class="${inp}">
            <input id="pf-days" type="number" min="1" placeholder="Duracion (dias)" value="${p.duration_days ?? ''}" class="${inp}">
            <input id="pf-desc" placeholder="Descripcion" value="${esc(p.description || '')}" class="${inp} md:col-span-2">
        </div>
        <div class="mt-4 flex gap-2">
            <button onclick="savePlan(${p.id ?? ''})" class="bg-[#1db954] hover:bg-[#1ed760] text-black px-5 py-2 rounded-full text-sm font-bold transition">Guardar</button>
            <button onclick="$('plan-form-wrap').innerHTML=''" class="border border-white/15 hover:border-white/40 px-5 py-2 rounded-full text-sm transition">Cancelar</button>
        </div>
    </div>`;
}
async function savePlan(id) {
    const body = {
        name: $('pf-name').value,
        application: $('pf-app').value,
        description: $('pf-desc').value,
        price: $('pf-price').value,
        duration_days: $('pf-days').value,
    };
    try {
        if (id) {
            await api('/membership-plans/' + id, { method: 'PUT', body });
            showAlert('Plan actualizado.');
        } else {
            await api('/membership-plans', { method: 'POST', body });
            showAlert('Plan creado.');
        }
        $('plan-form-wrap').innerHTML = '';
        renderPlans();
    } catch (err) { showAlert(err.message, false); }
}
async function deletePlan(id) {
    if (!confirm('¿Eliminar este plan?')) return;
    try { await api('/membership-plans/' + id, { method: 'DELETE' }); showAlert('Plan eliminado.'); renderPlans(); }
    catch (err) { showAlert(err.message, false); }
}
async function subscribe(planId) {
    try {
        await api('/subscriptions', { method: 'POST', body: { membership_plan_id: planId } });
        showAlert('Suscripcion creada correctamente.');
        go('subscriptions');
    } catch (err) { showAlert(err.message, false); }
}
async function cancelPlanSub(id) {
    if (!confirm('¿Cancelar esta suscripcion?')) return;
    try { await api('/subscriptions/' + id, { method: 'DELETE' }); showAlert('Suscripcion cancelada.'); renderPlans(); }
    catch (err) { showAlert(err.message, false); }
}

/* ---------- SUBSCRIPTIONS ---------- */
async function renderSubscriptions() {
    const box = $('tab-subscriptions');
    box.innerHTML = '<p class="text-zinc-500">Cargando...</p>';
    try {
        const subs = await api('/subscriptions');
        const items = subs.data ?? subs;
        const admin = user.role === 'admin';
        if (!items.length) { box.innerHTML = '<div class="bg-[#181818] border border-white/5 rounded-xl p-8 text-zinc-400 text-sm text-center">No tienes suscripciones. Ve a "Planes" para suscribirte.</div>'; return; }
        box.innerHTML = `<div class="bg-[#181818] border border-white/5 rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-black/30 text-left text-zinc-400 uppercase tracking-wider text-[11px]"><tr>
                <th class="px-5 py-3.5">#</th>${admin ? '<th class="px-5 py-3.5">Cliente</th>' : ''}<th class="px-5 py-3.5">Aplicacion</th><th class="px-5 py-3.5">Plan</th>
                <th class="px-5 py-3.5">Estado</th><th class="px-5 py-3.5">Inicio</th><th class="px-5 py-3.5">Fin</th>
                <th class="px-5 py-3.5">Proxima facturacion</th><th class="px-5 py-3.5"></th>
            </tr></thead><tbody>
            ${items.map(s => `<tr class="border-b border-white/5 last:border-0 hover:bg-white/[0.03]">
                <td class="px-5 py-3 text-zinc-400">${s.id}</td>${admin ? `<td class="px-5 py-3 text-zinc-400">${esc(s.user_id)}</td>` : ''}
                <td class="px-5 py-3">${appBadge(s.application)}</td>
                <td class="px-5 py-3 font-semibold">${esc(s.plan)}</td>
                <td class="px-5 py-3">${badge(s.status)}</td>
                <td class="px-5 py-3 text-zinc-400">${dt(s.starts_at)}</td><td class="px-5 py-3 text-zinc-400">${dt(s.ends_at)}</td>
                <td class="px-5 py-3 text-zinc-400">${dt(s.next_billing_date)}</td>
                <td class="px-5 py-3 text-right">${s.status === 'active' ? `<button onclick="cancelSub(${s.id})" class="text-red-400 hover:text-red-300 text-xs font-bold transition">Cancelar</button>` : ''}</td>
            </tr>`).join('')}
            </tbody></table></div></div>`;
    } catch (err) { box.innerHTML = '<p class="text-red-400">' + esc(err.message) + '</p>'; }
}
async function cancelSub(id) {
    if (!confirm('¿Cancelar esta suscripcion?')) return;
    try { await api('/subscriptions/' + id, { method: 'DELETE' }); showAlert('Suscripcion cancelada.'); renderSubscriptions(); }
    catch (err) { showAlert(err.message, false); }
}

/* ---------- INVOICES ---------- */
async function renderInvoices() {
    const box = $('tab-invoices');
    box.innerHTML = '<p class="text-zinc-500">Cargando...</p>';
    try {
        const invs = await api('/invoices');
        const items = invs.data ?? invs;
        const admin = user.role === 'admin';
        if (!items.length) { box.innerHTML = '<div class="bg-[#181818] border border-white/5 rounded-xl p-8 text-zinc-400 text-sm text-center">Sin facturas.</div>'; return; }
        box.innerHTML = `<div class="bg-[#181818] border border-white/5 rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-black/30 text-left text-zinc-400 uppercase tracking-wider text-[11px]"><tr>
                <th class="px-5 py-3.5">#</th><th class="px-5 py-3.5">Aplicacion</th><th class="px-5 py-3.5">Plan</th><th class="px-5 py-3.5">Monto</th>
                <th class="px-5 py-3.5">Estado</th><th class="px-5 py-3.5">Referencia</th><th class="px-5 py-3.5">Pagada</th><th class="px-5 py-3.5"></th>
            </tr></thead><tbody>
            ${items.map(i => `<tr class="border-b border-white/5 last:border-0 hover:bg-white/[0.03]">
                <td class="px-5 py-3 text-zinc-400">${i.id}</td>
                <td class="px-5 py-3">${appBadge(i.application)}</td>
                <td class="px-5 py-3 font-semibold">${esc(i.plan)}</td>
                <td class="px-5 py-3 font-bold">${fmt(i.amount)}</td>
                <td class="px-5 py-3">${badge(i.status)}</td>
                <td class="px-5 py-3 text-zinc-400">${esc(i.payment_reference || '-')}</td>
                <td class="px-5 py-3 text-zinc-400">${dt(i.paid_at)}</td>
                <td class="px-5 py-3">
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        ${!admin && i.status !== 'paid' ? `
                            <button onclick="payInvoice(${i.id})" class="bg-[#1db954] hover:bg-[#1ed760] text-black rounded-full px-4 py-1.5 text-xs font-bold transition">Pagar</button>
                            <button onclick="payInvoice(${i.id}, 'declined')" class="border border-red-500/40 text-red-400 hover:bg-red-500/10 rounded-full px-4 py-1.5 text-xs font-semibold transition">Simular rechazo</button>
                        ` : ''}
                        ${admin ? `<select onchange="changeInvoiceStatus(${i.id}, this.value)" class="bg-[#121212] border border-white/10 rounded-full px-3 py-1.5 text-xs text-white focus:outline-none">
                            ${['pending','paid','failed'].map(s => `<option value="${s}" ${s === i.status ? 'selected' : ''}>${s}</option>`).join('')}
                        </select>` : ''}
                        <button onclick="downloadPdf(${i.id})" title="Descargar PDF" class="border border-white/15 hover:border-[#1db954] hover:text-[#1db954] rounded-full px-3 py-1.5 text-xs font-semibold transition inline-flex items-center gap-1.5">
                            ${I.pdf} PDF
                        </button>
                    </div>
                </td>
            </tr>`).join('')}
            </tbody></table></div></div>`;
    } catch (err) { box.innerHTML = '<p class="text-red-400">' + esc(err.message) + '</p>'; }
}
async function payInvoice(id, decision) {
    try {
        const body = { invoice_id: id };
        if (decision) body.simulate_decision = decision;
        await api('/payments', { method: 'POST', body });
        showAlert(decision === 'declined' ? 'Pago rechazado (simulado).' : 'Pago procesado correctamente.');
        renderInvoices();
        renderPayments();
    } catch (err) { showAlert(err.message, false); }
}
async function changeInvoiceStatus(id, status) {
    try { await api('/invoices/' + id + '/status', { method: 'PATCH', body: { status } }); showAlert('Estado actualizado.'); renderInvoices(); }
    catch (err) { showAlert(err.message, false); }
}
async function downloadPdf(id) {
    try {
        const res = await fetch(API + '/invoices/' + id + '/pdf', { headers: { 'Accept': 'application/pdf', 'Authorization': 'Bearer ' + token } });
        if (!res.ok) throw new Error('No se pudo generar el PDF.');
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'factura-' + String(id).padStart(6, '0') + '.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    } catch (err) { showAlert(err.message, false); }
}

/* ---------- PAYMENTS ---------- */
async function renderPayments() {
    const box = $('tab-payments');
    box.innerHTML = '<p class="text-zinc-500">Cargando...</p>';
    try {
        const pays = await api('/payments');
        const items = pays.data ?? pays;
        if (!items.length) { box.innerHTML = '<div class="bg-[#181818] border border-white/5 rounded-xl p-8 text-zinc-400 text-sm text-center">Sin pagos registrados.</div>'; return; }
        box.innerHTML = `<div class="bg-[#181818] border border-white/5 rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-black/30 text-left text-zinc-400 uppercase tracking-wider text-[11px]"><tr>
                <th class="px-5 py-3.5">#</th><th class="px-5 py-3.5">Factura</th><th class="px-5 py-3.5">Monto</th>
                <th class="px-5 py-3.5">Estado</th><th class="px-5 py-3.5">Pasarela</th><th class="px-5 py-3.5">Referencia</th><th class="px-5 py-3.5">Fecha</th>
            </tr></thead><tbody>
            ${items.map(p => `<tr class="border-b border-white/5 last:border-0 hover:bg-white/[0.03]">
                <td class="px-5 py-3 text-zinc-400">${p.id}</td><td class="px-5 py-3 text-zinc-400">#${p.invoice_id}</td>
                <td class="px-5 py-3 font-bold">${fmt(p.amount)}</td>
                <td class="px-5 py-3">${badge(p.status)}</td>
                <td class="px-5 py-3">${esc(p.gateway)}</td>
                <td class="px-5 py-3 text-zinc-400">${esc(p.reference || '-')}</td>
                <td class="px-5 py-3 text-zinc-400">${dt(p.created_at)}</td>
            </tr>`).join('')}
            </tbody></table></div></div>`;
    } catch (err) { box.innerHTML = '<p class="text-red-400">' + esc(err.message) + '</p>'; }
}

/* ---------- USERS (admin) ---------- */
async function renderUsers() {
    const box = $('tab-users');
    box.innerHTML = '<p class="text-zinc-500">Cargando...</p>';
    try {
        const res = await api('/users');
        const items = res.data ?? res;
        if (!items.length) { box.innerHTML = '<div class="bg-[#181818] border border-white/5 rounded-xl p-8 text-zinc-400 text-sm text-center">Sin usuarios.</div>'; return; }
        box.innerHTML = `<div class="bg-[#181818] border border-white/5 rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-black/30 text-left text-zinc-400 uppercase tracking-wider text-[11px]"><tr>
                <th class="px-5 py-3.5">Usuario</th><th class="px-5 py-3.5">Rol</th>
                <th class="px-5 py-3.5">Suscripciones</th><th class="px-5 py-3.5">Registrado</th><th class="px-5 py-3.5"></th>
            </tr></thead><tbody>
            ${items.map(u => `
            <tr class="border-b border-white/5 last:border-0 hover:bg-white/[0.03]">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 shrink-0 rounded-full bg-gradient-to-br from-[#1db954] to-emerald-800 flex items-center justify-center font-bold text-black text-xs">${esc((u.name || '?').split(/\s+/).slice(0, 2).map(w => (w[0] || '').toUpperCase()).join('') || '?')}</div>
                        <div class="min-w-0">
                            <p class="font-semibold truncate">${esc(u.name)} ${u.id === user.id ? '<span class="text-[10px] text-[#1db954] font-bold">(tu)</span>' : ''}</p>
                            <p class="text-xs text-zinc-500 truncate">${esc(u.email)}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3">${u.role === 'admin' ? badge('admin') : badge('client')}</td>
                <td class="px-5 py-3 text-zinc-400">${u.subscriptions_count ?? 0}</td>
                <td class="px-5 py-3 text-zinc-400">${dt(u.created_at)}</td>
                <td class="px-5 py-3 text-right">
                    ${u.id === user.id ? '<span class="text-xs text-zinc-500">Tu cuenta</span>'
                    : u.role === 'admin'
                        ? `<button onclick="changeUserRole(${u.id}, 'client')" class="border border-red-500/40 text-red-400 hover:bg-red-500/10 rounded-full px-4 py-1.5 text-xs font-semibold transition">Quitar admin</button>`
                        : `<button onclick="changeUserRole(${u.id}, 'admin')" class="bg-[#1db954] hover:bg-[#1ed760] text-black rounded-full px-4 py-1.5 text-xs font-bold transition">Hacer admin</button>`}
                </td>
            </tr>`).join('')}
            </tbody></table></div></div>`;
    } catch (err) { box.innerHTML = '<p class="text-red-400">' + esc(err.message) + '</p>'; }
}
async function changeUserRole(id, role) {
    if (!confirm('¿Cambiar el rol de este usuario a "' + role + '"?')) return;
    try {
        await api('/users/' + id + '/role', { method: 'PATCH', body: { role } });
        showAlert('Rol actualizado correctamente.');
        renderUsers();
    } catch (err) { showAlert(err.message, false); }
}

/* ---------- REPORTS ---------- */
async function renderReports() {
    const box = $('tab-reports');
    box.innerHTML = '<p class="text-zinc-500">Cargando...</p>';
    try {
        const r = await api('/reports');
        const rev = await api('/reports/revenue');
        box.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                ${card('Ingresos (periodo)', fmt(r.total_revenue))}
                ${card('Nuevas suscripciones', r.new_subscriptions)}
                ${card('Facturas pagadas', r.paid_invoices)}
                ${card('Facturas fallidas', r.failed_invoices)}
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                ${listBlock('Ingresos por mes (' + r.period.from + ' a ' + r.period.to + ')', (rev.by_month ?? []).map(m => ({ id: m.month, amount: m.total, count: m.count })), revRow)}
                <div class="bg-[#181818] border border-white/5 rounded-xl">
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-white/5">
                        <span class="text-[#1db954]">${I.chart}</span>
                        <h3 class="font-bold">Nuevas suscripciones por dia</h3>
                    </div>
                    <div class="p-5">${renderByDay(await api('/reports/subscriptions'))}</div>
                </div>
            </div>`;
    } catch (err) { box.innerHTML = '<p class="text-red-400">' + esc(err.message) + '</p>'; }
}
function monthLabel(m) {
    const [y, mm] = String(m).split('-');
    const names = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    return names[parseInt(mm, 10) - 1] + ' ' + y;
}
function revRow(m) {
    return `<tr class="border-b border-white/5 last:border-0 hover:bg-white/[0.03]">
        <td class="px-5 py-3 font-semibold">${esc(monthLabel(m.id))}</td>
        <td class="px-5 py-3 font-bold text-[#1ed760]">${fmt(m.amount)}</td>
        <td class="px-5 py-3 text-zinc-400">${m.count} pagos</td>
    </tr>`;
}
function renderByDay(data) {
    const days = data.by_day ?? [];
    if (!days.length) return '<p class="text-sm text-zinc-500">Sin datos.</p>';
    const max = Math.max(...days.map(x => x.total));
    return '<div class="flex items-end gap-1 h-32">' + days.map(d => {
        const h = Math.round((d.total / (max || 1)) * 100);
        return `<div class="flex-1 flex flex-col items-center gap-1">
            <span class="text-[10px] text-zinc-400">${d.total}</span>
            <div class="w-full bg-[#1db954] hover:bg-[#1ed760] rounded-t transition" style="height:${h}%"></div>
            <span class="text-[10px] text-zinc-500">${d.day.slice(5)}</span>
        </div>`;
    }).join('') + '</div>';
}

/* ---------- BOOT ---------- */
async function init() {
    if (!token) {
        $('auth-view').classList.remove('hidden');
        $('auth-view').classList.add('flex');
        $('app-view').classList.add('hidden');
        showAuthForm('login');
        return;
    }
    try {
        user = await api('/user');
        $('user-info').textContent = user.name || user.email;
        $('user-role').textContent = user.role === 'admin' ? 'Administrador' : 'Cliente';
        const initials = String(user.name || user.email || '?').trim().split(/\s+/).slice(0, 2).map(w => (w[0] || '').toUpperCase()).join('');
        $('user-avatar').textContent = initials || '?';
        if (user.role === 'admin') {
            $('tab-users').classList.remove('hidden');
            $('tab-reports').classList.remove('hidden');
        }
        $('auth-view').classList.add('hidden');
        $('auth-view').classList.remove('flex');
        $('app-view').classList.remove('hidden');
        go('dashboard');
    } catch (err) {
        clearSession();
        init();
    }
}

init();
</script>
</body>
</html>
@endverbatim
