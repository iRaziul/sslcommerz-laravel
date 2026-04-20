# Changelog

All notable changes to `sslcommerz-laravel` will be documented in this file.

## Clean up: Drop spatie/laravel-package-tools - 2026-04-20

What's Changed

- Refactoring: Removed spatie/laravel-package-tools dependency and migrated to a standard Laravel ServiceProvider implementation.
- Enhanced DX: Added a native InstallCommand with support for both sslcommerz-laravel:install and the new sslcommerz:install alias.
- Improved Internal Structure: The SslcommerzServiceProvider is now final and uses better-organized private methods for registering services and publishing assets.
- Documentation: Updated README.md to reflect the new installation commands.
- Tests: Refactored the service provider test suite to align with the new implementation.

Why?

- To reduce the package's weight and overhead by removing unnecessary dependencies, making it more lightweight and easier to maintain.

## v1.1.0 - 2026-03-23

### What's Changed

- Added Laravel 13 support to the package constraints
- Updated the test stack to support the Laravel 13 ecosystem
- Expanded the CI matrix to cover Laravel 10, 11, 12, and 13
- Corrected README badges and package metadata references
- Fixed GitHub Actions test execution by removing the Pest --ci flag that caused the coverage driver warning

### Compatibility

- Laravel 10.x, 11.x, 12.x, and 13.x
- PHP 8.2, 8.3, and 8.4

### Verification

- Composer dependency resolution updated for Laravel 13 support
- Test suite passes locally

## v1.0.2 - 2025-02-26

### What's Changed

* Support for Laravel 12.x, PHP 8.3/8.4 by @masroore in https://github.com/iRaziul/sslcommerz-laravel/pull/4

### New Contributors

* @masroore made their first contribution in https://github.com/iRaziul/sslcommerz-laravel/pull/4

**Full Changelog**: https://github.com/iRaziul/sslcommerz-laravel/compare/v1.0.1...v1.0.2
