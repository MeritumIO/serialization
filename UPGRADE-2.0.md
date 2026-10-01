# Upgrading from 1.x to 2.0

2.0 migrates `meritum/serialization` onto `georgeff/kernel` ^2.0. The serialization API itself (`Formatter`, `SerializerInterface`, `Item`/`Collection`, the strategies, pagination) is unchanged — what affects you here comes from upgrading the base kernel. **Read [`georgeff/kernel`'s own `UPGRADE-2.0.md`](https://github.com/MikeGeorgeff/kernel/blob/main/UPGRADE-2.0.md) first**; this guide only covers what's specific to `meritum/serialization`.

See `CHANGELOG.md` for the full list of changes.

## Requirements

- [ ] **`georgeff/kernel` ^2.0.** `composer.json` now requires `"georgeff/kernel": "^2.0"`. `SerializationModule` implements kernel 2.0's `Contract\ModuleInterface`, so it can't be added to a 1.x kernel.

## 1. Replacing `FormatterInterface` needs `override()`

Kernel 2.0's `define()` throws `DefinitionException` when an id is already defined. `SerializationModule` defines `FormatterInterface::class`, so defining it again yourself now fails instead of silently winning.

- [ ] If you only swap the strategy by defining `StrategyInterface::class`, no change is needed — that still works, and no longer depends on registration order.
- [ ] If you replace `FormatterInterface::class` itself, switch to `override()`:

  ```php
  // Before
  $kernel->define(FormatterInterface::class, fn () => new MyFormatter());

  // After
  $kernel->override(FormatterInterface::class, fn () => new MyFormatter());
  ```

## Verifying the upgrade

- [ ] `composer test` — full suite passes
- [ ] `composer analyze` — PHPStan clean at `level: max`
- [ ] Grep your own codebase for `define(FormatterInterface::class` — any match needs section 1.
- [ ] Also run through [`georgeff/kernel`'s own verification checklist](https://github.com/MikeGeorgeff/kernel/blob/main/UPGRADE-2.0.md#verifying-the-upgrade) for base-kernel-level changes.
