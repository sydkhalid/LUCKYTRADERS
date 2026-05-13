# LUCKY TRADERS Production Deployment

Use this checklist before live billing.

## Environment

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Use `.env.production.example` as the production environment template
- Configure production `APP_URL`
- Configure production database credentials
- Configure `DB_DUMP_BINARY_PATH` when the database dump binary is not available in `PATH`

## Commands

```bash
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder
php artisan storage:link
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Verification

- Run `php artisan test`
- Open ERP Settings > Testing Checklist
- Confirm backup creation as Super Admin
- Download and inspect one GST invoice PDF and one normal bill PDF
