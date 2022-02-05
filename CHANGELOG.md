# Instagram Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.0.6 - 2022-02-05
### Added
- Added Instagram image proxy (useful when using page caching)

## 1.0.5 - 2021-02-03
### Changed
- Made Facebook Graph API User access token redundant, as an App access token is now used

## 1.0.4 - 2021-01-20
### Added
- Added missing setError method in Token service

### Fixed
- Fixed an issue when an Instagram media isn’t available anymore
- Fixed uncaught Exceptions

## 1.0.3 - 2021-01-14
### Fixed
- Removed splitSingleWord argument when using Craft's truncate Twig filter

## 1.0.2 - 2021-01-14
### Fixed
- Fixed issue with argument name that changed in Craft's truncate Twig filter

## 1.0.1 - 2021-01-11
### Fixed
- Fixed issue with trailing comma in arguments list and PHP versions under 7.3

## 1.0.0 - 2020-12-15
### Added
- Initial release
