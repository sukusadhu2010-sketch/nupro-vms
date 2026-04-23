# VMS Pro Admin Dark Theme Overhaul - TODO

## Plan Implementation Steps

### Phase 1: Core Theme (CSS + Layout)
- [x] **1.1** Update `resources/css/app.css`: Add dark CSS vars (#1a1c23 base, #10b981 primary, glassmorphism, compact font 14px/line-height 1.25, table hovers, thin scrollbars).
- [x] **1.2** Edit `resources/views/layouts/app.blade.php`: Dark navbar/sidebar/main, JS force dark mode default.

### Phase 2: Admin Pages
- [x] **2.1** Edit `resources/views/dashboard/admin.blade.php`: Replace light cards/tables with dark classes, compact padding py-3.
- [x] **2.2** Edit `resources/views/admin/customers/index.blade.php`: Dark cards/tables, form-control-sm, py-3.
- [x] **2.3** Edit `resources/views/admin/products/index.blade.php`: Same as above.

### Phase 3: Rebuild & Test
- [x] **3.1** Run `npm run dev` to rebuild CSS (Note: Node upgrade recommended for Vite).

**Current Progress: Phase 2 Complete (2.1-2.3 ✅), CSS rebuilt**

**Next Step: 3.2 - Manual test pages in browser**
- [ ] **3.2** Test pages: /dashboard, /admin/customers, /admin/products – verify dark theme, full-width, hovers, compactness.
- [ ] **3.3** Scan/update other admin indexes if needed (vendors, users).

**Current Progress: Phase 1 (1.1 ✅)**

**Next Step: 1.2 - Update layouts/app.blade.php**
