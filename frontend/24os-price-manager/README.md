# 24os-price-manager

Angular 14 source for the **`plugin/main/`** admin app of the 24os Price Manager WordPress plugin.
See the [main README](../../README.md) for the overall project.

```bash
npm install
npm run build -- --configuration production --output-hashing=all --output-path ../../plugin/main
```

After building, update the hashed file names in `os24_price_manager_load_scripts()`
in `plugin/24os-price-manager.php` (see the main README).
