# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## [v2.1.0](https://github.com/zaphyr-org/logger/compare/2.0.2...2.1.0) [2024-05-03]

### New:
* Added NoopHandler class
* Added unit test for context interpolation on invalid context

## [v2.0.2](https://github.com/zaphyr-org/logger/compare/2.0.1...2.0.2) [2023-11-19]

### New:
* Added `.vscode/` to .gitignore file

### Changed:
* Improved unit tests and moved tests to "Unit" directory

### Removed:
* Removed phpstan-phpunit from composer require-dev

## [v2.0.1](https://github.com/zaphyr-org/logger/compare/2.0.0...2.0.1) [2023-10-13]

### New:
* Added provide section to composer.json
* Updated psr/log to v3.0

### Changed:
* Renamed phpunit.xml.dist to phpunit.xml
* Renamed `unit` to `phpunit` in composer.json scripts section

### Removed:
* Removed "/tests" directory from phpstan paths

### Fixed:
* Removed .dist from phpunit.xml in .gitattributes export-ignore

## [v2.0.0](https://github.com/zaphyr-org/logger/compare/1.0.1...2.0.0) [2023-06-19]

### New:
* First stable release version
