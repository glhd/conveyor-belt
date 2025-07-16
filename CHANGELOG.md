# Changelog

All notable changes will be documented in this file following the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) 
format. This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.2.0] - 2025-07-16

## [2.1.0] - 2024-03-12

## [2.0.0] - 2023-07-03

### Added

-   Added support for `filterRow()` and `rejectRow()` methods

### Changed

-   Requires PHP 8.0 or higher
-   Moved from `box\sprout` to `opensprout\opensprout` and upgraded to version 4

## [1.0.0] - 2023-02-17

### Added

-   Added Laravel 10 support

## [0.3.3] - 2023-01-31

### Fixed

-   Fixed issue where query builder was getting re-used during tests
-   Improved docblock comments for IDE users

## [0.3.2] - 2022-11-11

### Fixed

-   Fixed signature compatibility issue introduced in Laravel 9.36.0

## [0.3.1] - 2022-02-18

### Fixed

-   Fixed an issue where the progress bar would reappear after new output

## [0.3.0] - 2022-02-16

### Added

-   Added better support for JSON APIs
-   Added support for any `Enumerable` object (like `Collection` or `LazyCollection`)

## [0.2.0] - 2022-02-16

### Added

-   Added support for JSON files
-   Added support for CSV files
-   Added support for Excel spreadsheets

### Changed

-   All configuration has been moved to command properties rather than functions (see README.md for more info)
-   Refactored most of the internals to support many more source types
-   Exceptions will always be shown even when `$collect_exceptions` is enabled. If `$collect_exceptions`
    is enabled, Conveyor Belt will _also_ show the exceptions at the end of execution

## [0.1.0] - 2022-01-31

### Added

-   Added config
-   Added translations
-   Added a `--pause-on-error` option

## [0.0.1] - 2022-01-28

### Added

-   Initial release

# Keep a Changelog Syntax

-   `Added` for new features.
-   `Changed` for changes in existing functionality.
-   `Deprecated` for soon-to-be removed features.
-   `Removed` for now removed features.
-   `Fixed` for any bug fixes. 
-   `Security` in case of vulnerabilities.

[Unreleased]: https://github.com/glhd/conveyor-belt/compare/2.2.0...HEAD

[2.2.0]: https://github.com/glhd/conveyor-belt/compare/2.1.0...2.2.0

[2.1.0]: https://github.com/glhd/conveyor-belt/compare/2.0.0...2.1.0

[2.0.0]: https://github.com/glhd/conveyor-belt/compare/1.0.0...2.0.0

[1.0.0]: https://github.com/glhd/conveyor-belt/compare/0.3.3...1.0.0

[0.3.3]: https://github.com/glhd/conveyor-belt/compare/0.3.2...0.3.3

[0.3.2]: https://github.com/glhd/conveyor-belt/compare/0.3.1...0.3.2

[0.3.1]: https://github.com/glhd/conveyor-belt/compare/0.3.0...0.3.1

[0.3.0]: https://github.com/glhd/conveyor-belt/compare/0.2.0...0.3.0

[0.2.0]: https://github.com/glhd/conveyor-belt/compare/0.1.0...0.2.0

[0.1.0]: https://github.com/glhd/conveyor-belt/compare/0.0.1...0.1.0

[0.0.1]: https://github.com/glhd/conveyor-belt/compare/0.0.1...0.0.1
