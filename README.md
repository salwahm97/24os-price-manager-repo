# 24os Price Manager

A WordPress plugin for building and managing **price lists / menus** (restaurants, cafés, services, etc.) from a modern **Angular** admin interface, backed by a custom **WordPress REST API**.

> Version `1.0.4` · Requires WordPress with the REST API enabled · PHP 7.4+ recommended

---

## Features

- Create, edit, publish, trash and restore **price menus**
- Organise each menu into **categories** and **items** with drag-and-drop style ordering (sorting endpoints)
- Multiple **prices / sizes** per item, optional **images** (per item or per category)
- **Allergen** management (create / edit / delete, assign to items)
- Bulk tools: **automatic renumbering** of items and **price increase** by percentage or fixed value
- Front-end display through a **shortcode**: `[24osPriceMenu id=<menu_id> style=1]`
- **Visit statistics** per menu
- **Role-based access**: choose which WordPress roles can see the plugin
- **Multi-language admin UI** (English / German — see `assets/lang/`)
- Clean uninstall: custom tables, options and post meta are removed

## How it works

```
┌────────────────────────────┐        ┌──────────────────────────────────┐
│  Angular apps (admin UI)   │  HTTP  │  WordPress plugin (PHP)          │
│  main / menu / settings /  │ ─────▶ │  REST API  /wp-json/             │
│  recovery                  │ ◀───── │  24os-price-manager/v1/*         │
└────────────────────────────┘  JSON  │  ↓                               │
                                      │  Custom tables + CPT + taxonomy  │
                                      └──────────────────────────────────┘
```

The plugin ships **four separately built Angular applications**. Each one is mounted on a different admin page through a shortcode that returns `<app-root></app-root>` and enqueues the matching bundle:

| Admin page (slug)                   | Folder      | Shortcode                    | Purpose                                  |
|-------------------------------------|-------------|------------------------------|------------------------------------------|
| `24os-price-manager`                | `main/`     | `[price_manager_app]`        | List / filter / create menus             |
| `24os-price-manager-menu`           | `menu/`     | `[price_manager_menu_app]`   | Edit one menu: categories, items, styles, statistics |
| `24os-price-manager-recovery`       | `recovery/` | `[price_manager_recovery_app]` | Restore or permanently delete trashed menus |
| `24os-price-manager-settings`       | `settings/` | `[price_manager_settings_app]` | Permissions, language, allergens, DB reset (admins only) |

The Angular apps compute the API base URL at runtime from the current admin URL
(`location.href.split("wp-admin")[0] + "wp-json/24os-price-manager/v1/…"`), so **no URL or credential is hard-coded** and the plugin works on any domain or sub-folder install.

## Repository structure

```
.
├── README.md
├── plugin/                         # ← the WordPress plugin (installed as "24os-price-manager")
│   ├── 24os-price-manager.php      # Bootstrap: tables, menus, CPT, REST routes, shortcodes, AJAX
│   ├── assets/
│   │   ├── image.png               # Placeholder image
│   │   └── lang/                   # en.json / de.json UI strings
│   ├── main/                       # Built Angular app – menu list
│   ├── menu/                       # Built Angular app – menu editor
│   ├── recovery/                   # Built Angular app – trash / recovery
│   ├── settings/                   # Built Angular app – settings
│   └── post/
│       └── 24os_price_menu_script.js   # Front-end script: records a visit via admin-ajax
└── frontend/                       # Angular 14 source of the four admin apps
    ├── 24os-price-manager/             → builds into plugin/main
    ├── 24os-price-manager-menu/        → builds into plugin/menu
    ├── 24os-price-manager-recovery/    → builds into plugin/recovery
    └── 24os-price-manager-settings/    → builds into plugin/settings
```

> In the WordPress plugins directory the `plugin/` folder **must be named `24os-price-manager`**.

### Database

On activation the plugin creates five tables (with your WordPress table prefix, e.g. `wp_`):

| Table                           | Content                                           |
|---------------------------------|---------------------------------------------------|
| `{prefix}24os_price_manager_menu`       | Menu record, linked post, owner, trash/active flags |
| `{prefix}24os_price_manager_category`   | Categories of a menu (name, description, sort, image) |
| `{prefix}24os_price_manager_item`       | Items (number, name, description, allergens, prices JSON, sort, image) |
| `{prefix}24os_price_manager_allergen`   | Allergen codes and descriptions                   |
| `{prefix}24os_price_manager_statistics` | Visits (menu id, visitor IP, user agent, date)    |

It also registers the custom post type `24os_price_manager` (public slug `price-menu`), the taxonomy `24os_price_manager_categories` ("Menu Types", default term *Uncategorized Price Menu*), and the options `24os_price_manager_admin_permissions_option` and `24os_price_manager_language_option`.

### REST API

Namespace: `/wp-json/24os-price-manager/v1`

| Route | Methods | Description |
|-------|---------|-------------|
| `/menu` | GET, POST | List / create menus |
| `/menu/{id}` | GET, PUT/PATCH, DELETE | Read / update / trash a menu |
| `/active-menu` | POST | Publish / unpublish a menu |
| `/menu-recovery` | GET | List trashed menus |
| `/menu-recovery/{id}` | POST | Restore a trashed menu |
| `/style-menu/{id}` | GET, PUT/PATCH | Menu display style |
| `/renumber-menu/{id}` | GET | Renumber items automatically |
| `/inc-price-menu/{id}` | POST | Increase all prices (percentage / value) |
| `/image-menu/{id}` | GET, PUT/PATCH | Enable images on item or category level |
| `/categories` | POST | Create a category |
| `/categories/{id}` | GET, PUT/PATCH, DELETE | Read / update / delete a category |
| `/categories-and-items/{menu_id}` | GET | Full tree for the editor |
| `/sorting-categories` | POST | Save category order |
| `/items` | POST | Create an item |
| `/items/{category_id}` | GET | Items of a category |
| `/items/{id}` | GET, PUT/PATCH, DELETE | Read / update / delete an item |
| `/sorting-items` | POST | Save item order |
| `/allergen` | GET, POST | List / create allergens |
| `/allergen/{id}` | GET, PUT/PATCH, DELETE | Read / update / delete an allergen |
| `/statistics/{menu_id}` | GET | Visit statistics |
| `/permissions` | GET, POST | Roles allowed to use the plugin |
| `/lang` | GET, POST | Admin UI language |
| `/database` | GET | Reset plugin tables (admin only) ⚠️ destructive |

> `PUT/PATCH` = WordPress `EDITABLE` (POST, PUT, PATCH). Verbs are defined in the `register_rest_route()` calls in `24os-price-manager.php`.

---

## Installation

### Option A — From a ZIP (recommended)

1. Download or clone this repository.
2. Copy the `plugin/` folder and **rename the copy to `24os-price-manager`**.
3. Compress that renamed folder into `24os-price-manager.zip` (the ZIP root must contain the folder `24os-price-manager/`).
4. In WordPress go to **Plugins → Add New → Upload Plugin**, choose the ZIP and click **Install Now**.
5. Click **Activate Plugin**. The database tables are created automatically.

### Option B — Manual / FTP / SSH

```bash
git clone https://github.com/salwahm97/24os-price-manager.git
cp -r 24os-price-manager/plugin /path/to/wordpress/wp-content/plugins/24os-price-manager
```

Then activate it from **Plugins** in the WordPress admin (or `wp plugin activate 24os-price-manager` with WP-CLI).

> Do **not** clone the repository directly into `wp-content/plugins/`; the plugin lives in the `plugin/` sub-folder.
> The installed folder name must be exactly `24os-price-manager`: asset URLs are built from `plugins_url() . "/24os-price-manager"`.

### Local development with Docker (optional)

```bash
docker run -d --name wp-db -e MYSQL_ROOT_PASSWORD=change_me -e MYSQL_DATABASE=wordpress mysql:8
docker run -d --name wp --link wp-db:mysql -p 8080:80 \
  -e WORDPRESS_DB_HOST=mysql -e WORDPRESS_DB_USER=root \
  -e WORDPRESS_DB_PASSWORD=change_me -e WORDPRESS_DB_NAME=wordpress \
  -v "$PWD/plugin:/var/www/html/wp-content/plugins/24os-price-manager" \
  wordpress:latest
```

Open <http://localhost:8080>, finish the WordPress setup, then activate the plugin. (Replace `change_me` with your own throw-away password — never commit real credentials.)

## Usage

1. Open **Price Manager** in the WordPress admin sidebar.
2. Click **Create New**, enter a name and choose a menu type.
3. Open the menu, add **categories** and **items** (prices, sizes, allergens, images).
4. Press **publish/activate** — a WordPress post is created containing the shortcode.
5. Show a menu anywhere with:

   ```
   [24osPriceMenu id=1 style=1]
   ```

6. Under **Price Manager → Settings** (administrators) choose which roles may use the plugin, the language, manage allergens or reset the database.

## Developing the Angular front-end

The folders `plugin/main/`, `plugin/menu/`, `plugin/recovery/` and `plugin/settings/` contain **compiled output only**.
The sources are in `frontend/` (Angular 14, Node 16/18):

| Source project                          | Build output       |
|-----------------------------------------|--------------------|
| `frontend/24os-price-manager`           | `plugin/main`      |
| `frontend/24os-price-manager-menu`      | `plugin/menu`      |
| `frontend/24os-price-manager-recovery`  | `plugin/recovery`  |
| `frontend/24os-price-manager-settings`  | `plugin/settings`  |

```bash
cd frontend/<project>
npm install
npm run build -- --configuration production --output-hashing=all --output-path ../../plugin/<main|menu|recovery|settings>
```

Because Angular appends a content hash to every build (`main.<hash>.js`), **update the file names in `os24_price_manager_load_scripts()`** inside `plugin/24os-price-manager.php` after each build, otherwise WordPress will keep loading the old files (and delete the old hashed files from the output folder).

## Privacy note

The statistics feature stores the **visitor IP address and user agent** of each menu view. Depending on your jurisdiction (e.g. GDPR) you may need to mention this in your privacy policy, anonymise the IP, or disable the feature.

The admin apps also load **Roboto** and **Material Icons** from Google Fonts (`frontend/*/src/index.html`). For strict GDPR compliance, self-host these fonts.

## Security

- **Authentication on every REST route** – each route has a `permission_callback` (attached through the `rest_endpoints` filter): logged-in users only, and the user's role must be enabled in *Settings → permissions* (administrators always pass).
- **Administrator-only routes** – `/database`, `/permissions`, and every write to `/lang` and `/allergen`.
- **Per-user ownership** – non-admin users can only read or change menus they created (menu, categories, items, sorting, statistics, recovery). Administrators can manage all menus.
- **CSRF protection** – WordPress cookie authentication needs a valid `X-WP-Nonce`. The compiled Angular apps do not send one, so the plugin injects a small script before each bundle that adds the header to same-origin `wp-json` requests. No Angular rebuild is needed.
- **SQL** – numeric ids are cast to integers, the statistics query uses `%d` / `%s` placeholders, and the visit logger validates the menu id.
- **Output escaping** – the `[24osPriceMenu]` shortcode escapes names, descriptions and prices (`esc_html`) and image sources (only `http(s)` URLs and base64 `data:image/png|jpeg|gif|webp`).
- **Visitor logging** – uses `REMOTE_ADDR`; spoofable `X-Forwarded-For` / `Client-IP` headers are ignored. Behind a trusted proxy, return the real IP from the `wpb_get_ip` filter.
- **Language** – only the shipped languages (`en`, `de`) are accepted.

Please report vulnerabilities privately (GitHub *Security → Report a vulnerability*), not in public issues.

### Known limitations

- `/database` still resets data through a **GET** request because the compiled Angular app calls it that way (now admin-only and nonce-protected). Switch it to `POST`/`DELETE` when you rebuild the front-end.
- By default **all roles are enabled** in the permissions option — review *Settings → Who can see the plugin* after activation.
- With `WP_DEBUG` on, WordPress may log "route registered without a permission callback" notices, because the callbacks are attached afterwards by the filter.
- The security layer was unit-tested with stubs, not yet on a live WordPress — try it on a staging site first.

## Contributing

Issues and pull requests are welcome. Please never include real credentials, API keys, database dumps or customer data in commits.

## License

No license is granted yet — all rights reserved.

## Author

Developed by **salwahm97** — <https://github.com/salwahm97>
