# pos-cafe — Component-Based Architecture

A clean, layered UI/organization layer for the Bird's Nest café POS. It sits **on top of** the existing working app and reuses its single source of truth (`../config.php`) for the database connection, RBAC (`can()`), settings constants, and schema migrations — so there is **no divergence** and nothing in the running system breaks.

> **Status:** the **foundation** and one complete **Products** vertical slice are fully implemented and runnable. The remaining modules (orders, inventory, employees, roles, reports, loyalty, announcements, categories, settings) have their folders scaffolded and are meant to be built by **copying the Products slice** (see *Adding a module* below).

---

## How to run

1. Ensure XAMPP (Apache + MySQL) is running and the `db_coffeeshop_final` database exists (your current app already uses it).
2. Log in through the **existing** app (`/FinalSystem/login.php`) — pos-cafe shares that session.
3. Open **`http://localhost/FinalSystem/pos-cafe/`**.
   - It routes you to the Products page (or the dashboard) based on your permissions.

If your project is not served from `/FinalSystem`, edit the two URL bases at the top of [`config/app.php`](config/app.php):

```php
define('ROOT_URL', '/FinalSystem');            // legacy app root
define('BASE_URL', ROOT_URL . '/pos-cafe');    // this app
```

---

## Request lifecycle

Every page/API starts by including the bootstrap, then composes middleware:

```php
require __DIR__ . '/../../config/app.php';   // session + root config.php ($conn, can(), constants) + autoloader + helpers
require POS_ROOT . '/middleware/auth.php';   // must be logged in
$required_permission = 'products';           // capability gate
require POS_ROOT . '/middleware/permission.php';
```

Then it uses a **model** for data and **components** for markup:

```php
$result = (new Product())->paginate(['search' => $s], $page);
component('layout/header', ['pageTitle' => 'Products']);
component('products/product-grid', ['products' => $result['rows'], 'canManage' => can('products')]);
component('layout/footer');
```

---

## Directory map

```
pos-cafe/
├── config/
│   ├── app.php          ✅ bootstrap (session, loads ../config.php, autoloader, helpers)
│   ├── database.php     ✅ db() accessor over the shared mysqli $conn
│   └── constants.php    ✅ UI constants (PER_PAGE, palette, STATUS_BADGES)
├── includes/
│   ├── auth.php         ✅ login guard
│   ├── functions.php    ✅ json_response, input, csrf_*, slug, time_ago
│   ├── permissions.php  ✅ require_can(), can_any() over can()
│   ├── helpers.php      ✅ e(), url(), asset(), component(), money(), flash(), redirect()
│   └── validators.php   ✅ Validator (required/numeric/min/max)
├── classes/
│   ├── Database.php     ✅ mysqli wrapper (all/first/scalar/insert/execute)
│   ├── Auth.php         ✅ session user (name/role/roleMeta/initials)
│   ├── Product.php      ✅ REFERENCE MODEL — full CRUD + paginate + search + toggle
│   ├── Category.php     ✅ active/find/options
│   └── Order, Employee, Role, User, Inventory, Report, Loyalty, …   ⬜ copy Product.php
├── middleware/
│   ├── auth.php         ✅   role.php ✅   permission.php ✅
├── components/
│   ├── layout/          ✅ header, sidebar (permission-gated), footer, main
│   ├── common/          ✅ alerts, toast, modal, pagination, theme-toggle, breadcrumbs, loading-spinner, empty-state
│   ├── products/        ✅ product-card, product-grid, product-form, product-search
│   └── orders/ inventory/ employees/ … ⬜ scaffolded
├── pages/
│   ├── products/        ✅ index, create, edit, delete
│   └── orders/ inventory/ employees/ … ⬜ scaffolded
├── api/
│   ├── products.php     ✅ list / search / toggle (JSON)
│   └── orders.php inventory.php …       ⬜ scaffolded
└── assets/  css/ js/ images/            ✅ app.js, custom.css (UI is Tailwind CDN)
```

✅ built & runnable · ⬜ folder created, implement by copying the Products slice

---

## Adding a module (copy the Products slice)

Say you want **Categories**. Do the same four moves that Products already demonstrates:

1. **Model** — `classes/Category.php` already exists; add `paginate/create/update/delete` mirroring `Product`.
2. **Components** — copy `components/products/*` → `components/categories/*` and adjust fields.
3. **Pages** — copy `pages/products/{index,create,edit,delete}.php` → `pages/categories/`; change the model, `$required_permission` (`'categories'`), and `$navActive`.
4. **API** (optional) — copy `api/products.php` → `api/categories.php`.
5. **Sidebar** — the link already exists in [`components/layout/sidebar.php`](components/layout/sidebar.php); point its `url` at the new page.

Permission slugs come from your real `permissions` table (`dashboard`, `find_orders`, `view_orders`, `products`, `categories`, `ingredients`, `employees`, `manage_roles`, `report`, `loyalty`, …). Use them in `$required_permission` and `can()`.

---

## Conventions

- **Models** (`classes/`) own all SQL and use **prepared statements** via the `Database` wrapper. No SQL in pages/components.
- **Components** (`components/`) are pure-presentation partials rendered with `component('path', [$data])`. No business logic.
- **Escaping**: always `e()` on output; forms carry `csrf_field()` and verify with `csrf_verify()`.
- **Theme**: amber `#F59E0B` primary, teal `#0F766E` active nav, `#111827` sidebar, Inter font, Tailwind `class` dark mode. Config lives in `components/layout/header.php`.
- **Auth/RBAC** is delegated to the root app — never re-implement login or `can()` here.

---

## Why it reuses `../config.php`

That file is the single source of truth for credentials, the `settings`-driven constants (`TAX_RATE`, `HAPPY_HOUR_*`, `KHR_RATE`, …), the RBAC `can()` implementation, and a self-applying migration system. Duplicating any of it would create two sources of truth that drift apart. pos-cafe therefore **wraps** it (OOP models, clean components) rather than replacing it — letting you migrate page-by-page with zero downtime.
