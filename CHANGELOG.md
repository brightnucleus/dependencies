# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [0.3.1] - 2016-08-17
### Added
- Add documentation about 'localize' key.

## [0.3.0] - 2016-08-17
### Added
- Added LICENSE file.

### Changed
- Change license to MIT.
- Don't force WordPress functions into global namespace.

### Removed
- Removed git pre-commit script.
- Removed unsued unit test preparations for now.

## [0.2.7] - 2016-06-30
### Changed
- Remove `beberlei/assert`.
- Update Composer dependencies.

## [0.2.6] - 2016-04-05
### Changed
- Update Composer dependencies.

## [0.2.5] - 2016-03-22
### Fixed
- Switch `beberlei/assert` back to official branch. Issue [#138](https://github.com/beberlei/assert/issues/138) has been fixed with v2.5.

## [0.2.4] - 2016-03-04
### Added
- Documentation improvements.

### Fixed
- Refactored enqueue_handle().
- Make sure we only pass an array to `invokeFunction()`.
- Switch `beberlei/assert` to own fork until [#138](https://github.com/beberlei/assert/issues/138) has been fixed.
- Several type-hinting tweaks.

## [0.2.3] - 2016-01-25
### Added
- Enqueueing of dependencies can now fall back to handles registered outside of `DependencyManager`.

## [0.2.2] - 2016-01-25
### Added
- Dependencies can now be enqueued individually, and separately from `register()`.
- `enqueue_handle()` has been added to enqueue one single dependency.
- Enqueuing supports priorities now.

## [0.2.1] - 2016-01-18
### Fixed
- Fixed dependency handlers.
- Fixed `$context` passing and validation.
- Bumped version requirements of `brightnucleus/exceptions` and related packages.

## [0.2.0] - 2016-01-17
### Fixed
- Switched to `brightnucleus/config` v0.2+.
- Removed `$config_key` from constructor & `processConfig()`.

## [0.1.3] - 2016-01-17
### Added
- Added `DependencyManagerInterface`.

### Fixed
- Helper methods for registering & enqueueing are now protected.

## [0.1.2] - 2016-01-17
### Fixed
- Make WPCS a dev-only requirement in composer.

## [0.1.1] - 2016-01-17
### Fixed
- Fixed changelog
- Fixed packagist badges.

## [0.1.0] - 2016-01-17
### Added
- Initial release to GitHub.

[0.3.1]: https://github.com/brightnucleus/dependencies/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/brightnucleus/dependencies/compare/v0.2.7...v0.3.0
[0.2.7]: https://github.com/brightnucleus/dependencies/compare/v0.2.6...v0.2.7
[0.2.6]: https://github.com/brightnucleus/dependencies/compare/v0.2.5...v0.2.6
[0.2.5]: https://github.com/brightnucleus/dependencies/compare/v0.2.4...v0.2.5
[0.2.4]: https://github.com/brightnucleus/dependencies/compare/v0.2.3...v0.2.4
[0.2.3]: https://github.com/brightnucleus/dependencies/compare/v0.2.2...v0.2.3
[0.2.2]: https://github.com/brightnucleus/dependencies/compare/v0.2.1...v0.2.2
[0.2.1]: https://github.com/brightnucleus/dependencies/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/brightnucleus/dependencies/compare/v0.1.3...v0.2.0
[0.1.3]: https://github.com/brightnucleus/dependencies/compare/v0.1.2...v0.1.3
[0.1.2]: https://github.com/brightnucleus/dependencies/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/brightnucleus/dependencies/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/brightnucleus/dependencies/compare/v0.0.0...v0.1.0
