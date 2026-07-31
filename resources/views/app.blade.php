@verbatim
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/tailwindcss">
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .status-active { @apply bg-green-100 text-green-700; }
        .status-pending { @apply bg-amber-100 text-amber-700; }
        .status-cancelled { @apply bg-gray-200 text-gray-600; }
        .status-expired, .status-failed { @apply bg-red-100 text-red-700; }
        .status-paid, .status-succeeded { @apply bg-green-100 text-green-700; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">

    <!-- AUTH VIEW -->
    <div id="auth-view" class="hidden min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-2xl font-bold text-center text-indigo-600 mb-1">Subscription Hub</h1>
            <p class="text-center text-sm text-slate-500 mb-6">Gestion de suscripciones y cobros recurrentes</p>

            <div class="flex mb-6 bg-slate-100 rounded-lg p-1">
                <button id="tab-login" onclick="showAuthForm('login')" class="flex-1 py-2 rounded-md font-medium auth-tab">Iniciar sesion</button>
                <button id="tab-register" onclick="showAuthForm('register')" class="flex-1 py-2 rounded-md font-medium auth-tab">Registrarse</button>
            </div>

            <form id="login-form" class="space-y-4" onsubmit="doLogin(event)">
                <input id="login-email" type="email" placeholder="Correo electronico" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                <input id="login-password" type="password" placeholder="Contrasena" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg py-2.5">Entrar</button>
            </form>

            <form id="register-form" class="hidden space-y-4" onsubmit="doRegister(event)">
                <input id="reg-name" type="text" placeholder="Nombre completo" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                <input id="reg-email" type="email" placeholder="Correo electronico" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                <input id="reg-password" type="password" placeholder="Contrasena (minimo 8)" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" required minlength="8">
                <input id="reg-password-confirm" type="password" placeholder="Confirmar contrasena" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" required minlength="8">
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg py-2.5">Crear cuenta</button>
            </form>

            <p id="auth-error" class="hidden mt-4 text-sm text-red-600 bg-red-50 rounded-lg p-3"></p>
            <p class="mt-6 text-xs text-center text-slate-400">
                Demo: admin@example.com / password (admin) &middot; cliente@example.com / password (cliente)
            </p>
        </div>
    </div>

    <!-- APP VIEW -->
    <div id="app-view" class="hidden">
        <header class="bg-indigo-600 text-white shadow">
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                <h1 class="text-xl font-bold">Subscription Hub</h1>
                <div class="flex items-center gap-4 text-sm">
                    <span id="user-info" class="bg-indigo-500 px-3 py-1 rounded-full"></span>
                    <button onclick="logout()" class="bg-white text-indigo-700 font-semibold px-4 py-1.5 rounded-lg hover:bg-indigo-50">Salir</button>
                </div>
            </div>
            <nav class="max-w-6xl mx-auto px-4 flex gap-2 pb-3 overflow-x-auto">
                <button data-tab="dashboard" onclick="go('dashboard')" class="nav-tab px-4 py-2 rounded-lg font-medium text-sm whitespace-nowrap">Dashboard</button>
                <button data-tab="plans" onclick="go('plans')" class="nav-tab px-4 py-2 rounded-lg font-medium text-sm whitespace-nowrap">Planes</button>
                <button data-tab="subscriptions" onclick="go('subscriptions')" class="nav-tab px-4 py-2 rounded-lg font-medium text-sm whitespace-nowrap">Suscripciones</button>
                <button data-tab="invoices" onclick="go('invoices')" class="nav-tab px-4 py-2 rounded-lg font-medium text-sm whitespace-nowrap">Facturas</button>
                <button data-tab="payments" onclick="go('payments')" class="nav-tab px-4 py-2 rounded-lg font-medium text-sm whitespace-nowrap">Pagos</button>
                <button data-tab="reports" id="tab-reports" onclick="go('reports')" class="nav-tab px-4 py-2 rounded-lg font-medium text-sm whitespace-nowrap hidden">Reportes</button>
            </nav>
        </header>

        <main class="max-w-6xl mx-auto px-4 py-6">
            <div id="alert" class="hidden mb-4 rounded-lg p-3 text-sm"></div>
            <section id="tab-dashboard" class="tab-content"></section>
            <section id="tab-plans" class="tab-content hidden"></section>
            <section id="tab-subscriptions" class="tab-content hidden"></section>
            <section id="tab-invoices" class="tab-content hidden"></section>
            <section id="tab-payments" class="tab-content hidden"></section>
            <section id="tab-reports" class="tab-content hidden"></section>
        </main>
    </div>

<script>
const API = '/api';
let token = localStorage.getItem('sh_token') || null;
let user = null;

const $ = (id) => document.getElementById(id);

function fmt(n) { return '$' + Number(n).toFixed(2); }
function dt(s) { return s ? String(s).slice(0, 10) : '-'; }
function esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function badge(status) { return `<span class="px-2 py-0.5 rounded-full text-xs font-semibold status-${esc(status)}">${esc(status)}</span>`; }

function showAlert(msg, ok = true) {
    const a = $('alert');
    a.classList.remove('hidden');
    a.className = 'mb-4 rounded-lg p-3 text-sm ' + (ok ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700');
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
        t.classList.toggle('bg-white', t.id === 'tab-' + which);
        t.classList.toggle('shadow', t.id === 'tab-' + which);
        t.classList.toggle('text-indigo-600', t.id === 'tab-' + which);
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
    showAuthForm('login');
}

/* ---------- NAV ---------- */
function go(tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
    document.querySelectorAll('.nav-tab').forEach(t => {
        t.classList.toggle('bg-white/20', t.dataset.tab === tab);
    });
    const target = $('tab-' + tab);
    target.classList.remove('hidden');
    const render = { dashboard: renderDashboard, plans: renderPlans, subscriptions: renderSubscriptions, invoices: renderInvoices, payments: renderPayments, reports: renderReports };
    render[tab]();
}

/* ---------- DASHBOARD ---------- */
async function renderDashboard() {
    const box = $('tab-dashboard');
    box.innerHTML = '<p class="text-slate-500">Cargando...</p>';
    try {
        const d = await api('/dashboard');
        if (user.role === 'admin') {
            box.innerHTML = `
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
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
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    ${card('Suscripcion activa', s ? 'Si' : 'Ninguna')}
                    ${card('Total suscripciones', d.total_subscriptions)}
                    ${card('Proxima facturacion', dt(d.next_billing_date))}
                    ${card('Total pagado', fmt(d.paid_amount))}
                </div>
                ${s ? `
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-semibold text-lg mb-2">Tu suscripcion</h3>
                    <p class="text-sm text-slate-600">Estado: ${badge(s.status)}</p>
                    <p class="text-sm text-slate-600 mt-1">Inicio: ${dt(s.starts_at)} &middot; Fin: ${dt(s.ends_at)} &middot; Proxima facturacion: ${dt(s.next_billing_date)}</p>
                </div>` : ''}`;
        }
    } catch (err) { box.innerHTML = '<p class="text-red-600">' + esc(err.message) + '</p>'; }
}

function card(label, value) {
    return `<div class="bg-white rounded-xl shadow p-4"><p class="text-xs text-slate-500 uppercase font-semibold">${esc(label)}</p><p class="text-2xl font-bold text-slate-800 mt-1">${value}</p></div>`;
}
function listBlock(title, items, rowFn) {
    if (!items.length) return `<div class="bg-white rounded-xl shadow p-4"><h3 class="font-semibold mb-2">${esc(title)}</h3><p class="text-sm text-slate-400">Sin registros.</p></div>`;
    return `<div class="bg-white rounded-xl shadow overflow-hidden"><h3 class="font-semibold px-4 py-3 border-b">${esc(title)}</h3><div class="overflow-x-auto"><table class="w-full text-sm"><tbody>${items.map(rowFn).join('')}</tbody></table></div></div>`;
}
function subRow(s) {
    return `<tr class="border-b border-slate-100"><td class="px-4 py-2">#${s.id}</td><td class="px-4 py-2">${esc(s.plan)}</td><td class="px-4 py-2">${badge(s.status)}</td><td class="px-4 py-2">${dt(s.next_billing_date)}</td></tr>`;
}
function invRow(i) {
    return `<tr class="border-b border-slate-100"><td class="px-4 py-2">#${i.id}</td><td class="px-4 py-2">${esc(i.plan)}</td><td class="px-4 py-2">${fmt(i.amount)}</td><td class="px-4 py-2">${badge(i.status)}</td></tr>`;
}

/* ---------- PLANS ---------- */
async function renderPlans() {
    const box = $('tab-plans');
    box.innerHTML = '<p class="text-slate-500">Cargando...</p>';
    try {
        const plans = await api('/membership-plans');
        localStorage.setItem('sh_plans_cache', JSON.stringify(plans.data ?? plans));
        const admin = user.role === 'admin';
        box.innerHTML = `
            ${admin ? `<div class="mb-4"><button onclick="newPlanForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">+ Nuevo plan</button></div>` : ''}
            <div id="plan-form-wrap"></div>
            <div class="grid md:grid-cols-3 gap-4">
                ${(plans.data ?? plans).map(p => `
                <div class="bg-white rounded-xl shadow p-5 flex flex-col">
                    <div class="flex items-start justify-between">
                        <h3 class="font-bold text-lg">${esc(p.name)}</h3>
                        ${p.status === 1 || p.status === true ? badge('active') : badge('cancelled')}
                    </div>
                    <p class="text-sm text-slate-500 mt-1">${esc(p.description || '')}</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-3">${fmt(p.price)} <span class="text-sm font-normal text-slate-400">/ ${p.duration_days} dias</span></p>
                    <div class="mt-auto pt-4 flex gap-2">
                        ${admin ? `
                            <button onclick="editPlanForm(${p.id})" class="flex-1 border border-slate-300 rounded-lg py-2 text-sm">Editar</button>
                            <button onclick="deletePlan(${p.id})" class="flex-1 bg-red-600 text-white rounded-lg py-2 text-sm">Eliminar</button>`
                        : `<button onclick="subscribe(${p.id})" class="flex-1 bg-indigo-600 text-white rounded-lg py-2 text-sm font-semibold">Suscribirme</button>`}
                    </div>
                </div>`).join('')}
            </div>`;
    } catch (err) { box.innerHTML = '<p class="text-red-600">' + esc(err.message) + '</p>'; }
}

function newPlanForm() {
    $('plan-form-wrap').innerHTML = planFormHtml();
}
function editPlanForm(id) {
    const p = (JSON.parse(localStorage.getItem('sh_plans_cache') || '[]')).find(x => x.id === id) || {};
    $('plan-form-wrap').innerHTML = planFormHtml(p);
}
function planFormHtml(p = {}) {
    return `<div class="bg-white rounded-xl shadow p-5 mb-4">
        <h3 class="font-semibold mb-3">${p.id ? 'Editar plan' : 'Nuevo plan'}</h3>
        <div class="grid md:grid-cols-5 gap-3">
            <input id="pf-name" placeholder="Nombre" value="${esc(p.name || '')}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <input id="pf-desc" placeholder="Descripcion" value="${esc(p.description || '')}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm md:col-span-2">
            <input id="pf-price" type="number" step="0.01" min="0" placeholder="Precio" value="${p.price ?? ''}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <input id="pf-days" type="number" min="1" placeholder="Dias" value="${p.duration_days ?? ''}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="mt-3 flex gap-2">
            <button onclick="savePlan(${p.id ? 'null' : ''})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Guardar</button>
            <button onclick="$('plan-form-wrap').innerHTML=''" class="border border-slate-300 px-4 py-2 rounded-lg text-sm">Cancelar</button>
        </div>
    </div>`;
}
async function savePlan() {
    const body = {
        name: $('pf-name').value,
        description: $('pf-desc').value,
        price: $('pf-price').value,
        duration_days: $('pf-days').value,
    };
    try {
        const data = await api('/membership-plans', { method: 'POST', body });
        showAlert('Plan creado.');
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

/* ---------- SUBSCRIPTIONS ---------- */
async function renderSubscriptions() {
    const box = $('tab-subscriptions');
    box.innerHTML = '<p class="text-slate-500">Cargando...</p>';
    try {
        const subs = await api('/subscriptions');
        const items = subs.data ?? subs;
        const admin = user.role === 'admin';
        if (!items.length) { box.innerHTML = '<div class="bg-white rounded-xl shadow p-6 text-slate-500 text-sm">No tienes suscripciones. Ve a la pestana "Planes" para suscribirte.</div>'; return; }
        box.innerHTML = `<div class="bg-white rounded-xl shadow overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500"><tr>
                <th class="px-4 py-3">#</th>${admin ? '<th class="px-4 py-3">Cliente</th>' : ''}<th class="px-4 py-3">Plan</th>
                <th class="px-4 py-3">Estado</th><th class="px-4 py-3">Inicio</th><th class="px-4 py-3">Fin</th>
                <th class="px-4 py-3">Proxima facturacion</th><th class="px-4 py-3"></th>
            </tr></thead><tbody>
            ${items.map(s => `<tr class="border-b border-slate-100">
                <td class="px-4 py-2.5">${s.id}</td>${admin ? `<td class="px-4 py-2.5">${esc(s.user_id)}</td>` : ''}
                <td class="px-4 py-2.5">${esc(s.plan)}</td>
                <td class="px-4 py-2.5">${badge(s.status)}</td>
                <td class="px-4 py-2.5">${dt(s.starts_at)}</td><td class="px-4 py-2.5">${dt(s.ends_at)}</td>
                <td class="px-4 py-2.5">${dt(s.next_billing_date)}</td>
                <td class="px-4 py-2.5 text-right">${s.status === 'active' ? `<button onclick="cancelSub(${s.id})" class="text-red-600 text-xs font-semibold">Cancelar</button>` : ''}</td>
            </tr>`).join('')}
            </tbody></table></div></div>`;
    } catch (err) { box.innerHTML = '<p class="text-red-600">' + esc(err.message) + '</p>'; }
}
async function cancelSub(id) {
    if (!confirm('¿Cancelar esta suscripcion?')) return;
    try { await api('/subscriptions/' + id, { method: 'DELETE' }); showAlert('Suscripcion cancelada.'); renderSubscriptions(); }
    catch (err) { showAlert(err.message, false); }
}

/* ---------- INVOICES ---------- */
async function renderInvoices() {
    const box = $('tab-invoices');
    box.innerHTML = '<p class="text-slate-500">Cargando...</p>';
    try {
        const invs = await api('/invoices');
        const items = invs.data ?? invs;
        const admin = user.role === 'admin';
        if (!items.length) { box.innerHTML = '<div class="bg-white rounded-xl shadow p-6 text-slate-500 text-sm">Sin facturas.</div>'; return; }
        box.innerHTML = `<div class="bg-white rounded-xl shadow overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500"><tr>
                <th class="px-4 py-3">#</th><th class="px-4 py-3">Plan</th><th class="px-4 py-3">Monto</th>
                <th class="px-4 py-3">Estado</th><th class="px-4 py-3">Referencia</th><th class="px-4 py-3">Pagada</th><th class="px-4 py-3"></th>
            </tr></thead><tbody>
            ${items.map(i => `<tr class="border-b border-slate-100">
                <td class="px-4 py-2.5">${i.id}</td><td class="px-4 py-2.5">${esc(i.plan)}</td>
                <td class="px-4 py-2.5 font-semibold">${fmt(i.amount)}</td>
                <td class="px-4 py-2.5">${badge(i.status)}</td>
                <td class="px-4 py-2.5">${esc(i.payment_reference || '-')}</td>
                <td class="px-4 py-2.5">${dt(i.paid_at)}</td>
                <td class="px-4 py-2.5 text-right">
                    ${!admin && i.status !== 'paid' ? `<button onclick="payInvoice(${i.id})" class="bg-green-600 text-white rounded-lg px-3 py-1.5 text-xs font-semibold">Pagar</button>` : ''}
                    ${admin ? `<select onchange="changeInvoiceStatus(${i.id}, this.value)" class="border border-slate-300 rounded-lg px-2 py-1 text-xs">
                        ${['pending','paid','failed'].map(s => `<option value="${s}" ${s === i.status ? 'selected' : ''}>${s}</option>`).join('')}
                    </select>` : ''}
                </td>
            </tr>`).join('')}
            </tbody></table></div></div>`;
    } catch (err) { box.innerHTML = '<p class="text-red-600">' + esc(err.message) + '</p>'; }
}
async function payInvoice(id) {
    try {
        await api('/payments', { method: 'POST', body: { invoice_id: id } });
        showAlert('Pago procesado correctamente.');
        renderInvoices();
        renderPayments();
    } catch (err) { showAlert(err.message, false); }
}
async function changeInvoiceStatus(id, status) {
    try { await api('/invoices/' + id + '/status', { method: 'PATCH', body: { status } }); showAlert('Estado actualizado.'); renderInvoices(); }
    catch (err) { showAlert(err.message, false); }
}

/* ---------- PAYMENTS ---------- */
async function renderPayments() {
    const box = $('tab-payments');
    box.innerHTML = '<p class="text-slate-500">Cargando...</p>';
    try {
        const pays = await api('/payments');
        const items = pays.data ?? pays;
        if (!items.length) { box.innerHTML = '<div class="bg-white rounded-xl shadow p-6 text-slate-500 text-sm">Sin pagos registrados.</div>'; return; }
        box.innerHTML = `<div class="bg-white rounded-xl shadow overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500"><tr>
                <th class="px-4 py-3">#</th><th class="px-4 py-3">Factura</th><th class="px-4 py-3">Monto</th>
                <th class="px-4 py-3">Estado</th><th class="px-4 py-3">Pasarela</th><th class="px-4 py-3">Referencia</th><th class="px-4 py-3">Fecha</th>
            </tr></thead><tbody>
            ${items.map(p => `<tr class="border-b border-slate-100">
                <td class="px-4 py-2.5">${p.id}</td><td class="px-4 py-2.5">#${p.invoice_id}</td>
                <td class="px-4 py-2.5 font-semibold">${fmt(p.amount)}</td>
                <td class="px-4 py-2.5">${badge(p.status)}</td>
                <td class="px-4 py-2.5">${esc(p.gateway)}</td>
                <td class="px-4 py-2.5">${esc(p.reference || '-')}</td>
                <td class="px-4 py-2.5">${dt(p.created_at)}</td>
            </tr>`).join('')}
            </tbody></table></div></div>`;
    } catch (err) { box.innerHTML = '<p class="text-red-600">' + esc(err.message) + '</p>'; }
}

/* ---------- REPORTS ---------- */
async function renderReports() {
    const box = $('tab-reports');
    box.innerHTML = '<p class="text-slate-500">Cargando...</p>';
    try {
        const r = await api('/reports');
        const rev = await api('/reports/revenue');
        box.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                ${card('Ingresos (periodo)', fmt(r.total_revenue))}
                ${card('Nuevas suscripciones', r.new_subscriptions)}
                ${card('Facturas pagadas', r.paid_invoices)}
                ${card('Facturas fallidas', r.failed_invoices)}
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                ${listBlock('Ingresos por mes (' + r.period.from + ' a ' + r.period.to + ')', (rev.by_month ?? []).map(m => ({ id: m.month, amount: m.total, count: m.count })), revRow)}
                <div class="bg-white rounded-xl shadow p-5">
                    <h3 class="font-semibold mb-2">Nuevas suscripciones por dia</h3>
                    ${renderByDay(await api('/reports/subscriptions'))}
                </div>
            </div>`;
    } catch (err) { box.innerHTML = '<p class="text-red-600">' + esc(err.message) + '</p>'; }
}
function revRow(m) {
    return `<tr class="border-b border-slate-100"><td class="px-4 py-2">${esc(m.id)}</td><td class="px-4 py-2 font-semibold">${fmt(m.amount)}</td><td class="px-4 py-2">${m.count} pagos</td></tr>`;
}
function renderByDay(data) {
    const days = data.by_day ?? [];
    if (!days.length) return '<p class="text-sm text-slate-400">Sin datos.</p>';
    return '<div class="flex items-end gap-1 h-32">' + days.map(d => {
        const max = Math.max(...days.map(x => x.total));
        const h = Math.round((d.total / (max || 1)) * 100);
        return `<div class="flex-1 flex flex-col items-center gap-1"><div class="bg-indigo-500 rounded-t" style="height:${h}%"></div><span class="text-[10px] text-slate-400">${d.day.slice(5)}</span></div>`;
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
        $('user-info').textContent = user.name + ' (' + user.role + ')';
        if (user.role === 'admin') $('tab-reports').classList.remove('hidden');
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
