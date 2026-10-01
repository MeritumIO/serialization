# Changelog

All notable changes to `meritum/serialization` are documented here.

---

## [2.0.0] — 2026-09-30

2.0 migrates to `georgeff/kernel` ^2.0.

### Added
- `SerializationModule` registers `StrategyInterface::class` as a fallback definition resolving to `DataArrayStrategy`, so the default strategy can now be resolved from the container directly. A consumer's own `define(StrategyInterface::class, ...)` replaces the fallback regardless of where it's registered — the bootstrap, or any module added before or after `SerializationModule`

### Changed
- **Breaking:** migrated to `georgeff/kernel` ^2.0 — `SerializationModule` now implements `Georgeff\Kernel\Contract\ModuleInterface`, so it can only be added to a 2.0 kernel
- `FormatterInterface`'s factory now always resolves its strategy from the container via `StrategyInterface::class`, instead of checking `has()` and constructing `DataArrayStrategy` inline when nothing was defined. The default output is unchanged
