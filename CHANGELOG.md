# Changelog

All notable changes to this project will be documented in this file.

## [1.0.3] - 2026-01-27

### Fixed
- Fixed coding style issues for Moodle plugin CI
- Added GitHub Actions CI workflow

## [1.0.2] - 2026-01-27

### Fixed
- Replaced direct $_GET/$_POST access with optional_param() in process.php and ipn.php
- All user input is now properly sanitized using Moodle's built-in functions

## [1.0.1] - 2026-01-27

### Changed
- Converted pay.php from heredoc HTML to Moodle templates and Output API
- Added pay_redirect.mustache template for payment redirect page
- Added pay_redirect renderable class for template data

## [1.0.0] - 2026-01-22

### Added
- Initial release
- LibraPay payment gateway integration
- Support for RON currency
- Test mode support
- BACKREF (synchronous) callback handling
- IPN (asynchronous) callback handling
- P_SIGN signature validation using HMAC-SHA1
- Payment success/failure notifications (email + Moodle notifications)
- Privacy provider for GDPR compliance
- English and Romanian language support
- Database storage for transaction records
- Token-based verification for cross-domain redirects
