# Subscription Hub API

Sistema de gestion de suscripciones y cobros recurrentes (modo simulacion) construido con **Laravel 10** y **Sanctum**.

## Roles

- `admin` — Finance Administrator: gestiona planes, facturas, pagos, dashboard y reportes.
- `client` — Customer (Subscriber): se suscribe a planes y ve sus propias suscripciones, facturas y pagos.

## Instalacion

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Usuarios de ejemplo (seeder):

| Email | Password | Rol |
| --- | --- | --- |
| admin@example.com | password | admin |
| cliente@example.com | password | client |

## Frontend

La aplicacion incluye una interfaz web (una sola pagina, sin build steps) que consume la API. Solo abre `http://localhost:8000` en el navegador:

```
php artisan serve
```

Pantallas: login/registro, dashboard (admin o cliente), catalogo de planes, suscripciones, facturas (pagar/actualizar estado), pagos y reportes (solo admin). El token se guarda en `localStorage` y se envia como `Authorization: Bearer <token>`.

## Autenticacion

Todas las rutas excepto `/register`, `/login` y ver el catalogo de planes requieren el header `Authorization: Bearer <token>`.

- `POST /api/register` — registra un usuario y devuelve `token`.
- `POST /api/login` — inicia sesion y devuelve `token`.
- `POST /api/logout` — invalida el token actual.
- `GET /api/user` — datos del usuario autenticado.

## Endpoints

### Publicos
| Metodo | Ruta | Descripcion |
| --- | --- | --- |
| GET | `/api/membership-plans` | Lista los planes disponibles |
| GET | `/api/membership-plans/{id}` | Detalle de un plan |

### Suscripciones (cliente: solo las propias; admin: todas)
| Metodo | Ruta | Descripcion |
| --- | --- | --- |
| GET / POST | `/api/subscriptions` | Listar / crear suscripcion |
| GET / PUT / DELETE | `/api/subscriptions/{id}` | Ver / actualizar / cancelar |

Al crear una suscripcion se genera la **primera factura y se cobra inmediatamente** (pago inicial). Los periodos siguientes se cobran automaticamente en cada `next_billing_date` con el comando de renovacion.

### Facturas (Invoices)
| Metodo | Ruta | Descripcion |
| --- | --- | --- |
| GET | `/api/invoices` | Lista de facturas |
| GET | `/api/invoices/{id}` | Detalle de una factura |
| PATCH | `/api/invoices/{id}/status` | Cambiar estado (solo admin) |

### Pagos
| Metodo | Ruta | Descripcion |
| --- | --- | --- |
| GET | `/api/payments` | Lista de pagos |
| POST | `/api/payments` | Procesa el pago de una factura (`{ "invoice_id": 1 }`) |
| GET | `/api/payments/{id}` | Detalle de un pago |

### Dashboard
| Metodo | Ruta | Descripcion |
| --- | --- | --- |
| GET | `/api/dashboard` | Estadisticas (admin: MRR, suscriptores, renovaciones; cliente: su estado) |

### Reportes (solo admin)
| Metodo | Ruta | Descripcion |
| --- | --- | --- |
| GET | `/api/reports` | Resumen del periodo |
| GET | `/api/reports/revenue` | Ingresos agrupados por mes |
| GET | `/api/reports/subscriptions` | Suscripciones por estado y por dia |
| GET | `/api/reports/invoices` | Facturas por estado y monto |

Los reportes aceptan `?from=YYYY-MM-DD&to=YYYY-MM-DD` (por defecto: ultimos 30 dias).

### Gestion de planes (solo admin)
| Metodo | Ruta |
| --- | --- |
| POST | `/api/membership-plans` |
| PUT | `/api/membership-plans/{id}` |
| DELETE | `/api/membership-plans/{id}` |

## Cobros recurrentes (Cron)

El comando `subscriptions:process-renewals` revisa diariamente las suscripciones con `next_billing_date` vencido, genera la factura mensual, intenta el cobro y:

- **Pago exitoso** → renueva la suscripcion (extiende `ends_at` y `next_billing_date`).
- **Pago fallido** → marca la suscripcion como `expired`.

Ejecutar una vez:

```bash
php artisan subscriptions:process-renewals
```

Programarlo en el servidor (cada minuto delega en el scheduler de Laravel):

```cron
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

## Pagos simulados (Stripe Test Mode)

Por defecto usa **simulacion** (sin API keys): genera un pago `succeeded` con referencia `SIM-XXXX`. Para usar **Stripe Test Mode** (100% gratis, tarjetas de prueba, sin tarjetas reales):

```
PAYMENT_GATEWAY=stripe
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
PAYMENT_CURRENCY=usd
STRIPE_TEST_CARD=tok_visa
```

Variables de simulacion:
- `PAYMENT_GATEWAY=simulation` — modo simulacion (por defecto).
- `PAYMENT_FAILURE_RATE=0.2` — 20% de probabilidad de que un pago falle (para probar el flujo de expiracion).

La simulacion replica una pasarela real y puede **procesar** o **rechazar** el cobro. Al pagar una factura puedes forzar la decision con el campo `simulate_decision`:

```json
POST /api/payments
{ "invoice_id": 1, "simulate_decision": "approved" }   // cobro procesado
{ "invoice_id": 1, "simulate_decision": "declined" }   // cobro rechazado (factura failed)
```

En la interfaz, cada factura pendiente tiene los botones **Pagar** y **Simular rechazo**. Sin el campo, se usa `PAYMENT_FAILURE_RATE`.

## Tests

```bash
php artisan test
```

## Estructura

```
app/Console/Commands        Comando de renovacion programado
app/Http/Controllers/Api    Controladores de la API
app/Http/Middleware         CheckRole (roles y permisos)
app/Models                  User, MembershipPlan, Subscription, Invoice, Payment
app/Services                Logica de negocio (pagos, suscripciones, etc.)
config/payment.php          Configuracion del gateway de pago
```
