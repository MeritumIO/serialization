# meritum/serialization

Pluggable object serialization with item, collection, and pagination support.

## Requirements

- PHP 8.4+
- `georgeff/kernel` ^2.0

## Installation

```bash
composer require meritum/serialization
```

## Overview

The package is built around three concepts:

- **Serializer** — transforms a model into a plain array
- **Resource** — wraps a model (or iterable) with its serializer and optional metadata
- **Formatter** — accepts a resource, applies a format strategy, and returns a `JsonSerializable` envelope

The format strategy controls the output shape. Two strategies are provided out of the box, and you can supply your own by implementing `StrategyInterface`.

## Serializers

Implement `SerializerInterface` to define how a model maps to an array:

```php
use Meritum\Serialization\SerializerInterface;

final class UserSerializer implements SerializerInterface
{
    public function serialize(mixed $data): array
    {
        return [
            'id'    => $data->id,
            'name'  => $data->name,
            'email' => $data->email,
        ];
    }
}
```

To embed a related model, call the related serializer's `serialize()` directly. The formatter does not recurse — related data must be resolved to a plain array inside `serialize()`:

```php
final class PostSerializer implements SerializerInterface
{
    public function serialize(mixed $data): array
    {
        return [
            'id'     => $data->id,
            'title'  => $data->title,
            'author' => new UserSerializer()->serialize($data->author),
        ];
    }
}
```

## Formatter

`Formatter` is the entry point. Construct it with a `StrategyInterface` and call `format()` with an `Item` or `Collection`:

```php
use Meritum\Serialization\Formatter;
use Meritum\Serialization\Resource\Item;
use Meritum\Serialization\Strategy\DataArrayStrategy;

$formatter = new Formatter(new DataArrayStrategy());

$envelope = $formatter->format(new Item($user, new UserSerializer()));

// $envelope implements JsonSerializable — pass it directly to any JSON response
return new JsonResponse($envelope);
```

## Item

Wrap a single model in an `Item`:

```php
use Meritum\Serialization\Resource\Item;

$item = new Item($user, new UserSerializer());
$envelope = $formatter->format($item);
```

With `DataArrayStrategy` (the default), the output is:

```json
{
  "data": {
    "id": 1,
    "name": "Mike",
    "email": "mike@georgeff.co"
  }
}
```

## Collection

Wrap an iterable of models in a `Collection`. Any `iterable` is accepted, including generators:

```php
use Meritum\Serialization\Resource\Collection;

$collection = new Collection($users, new UserSerializer());
$envelope = $formatter->format($collection);
```

Output:

```json
{
  "data": [
    { "id": 1, "name": "Mike", "email": "mike@georgeff.co" },
    { "id": 2, "name": "Steve", "email": "steve@georgeff.co" }
  ]
}
```

## Metadata

Both `Item` and `Collection` accept call-site metadata via `addMeta()`. Multiple calls chain fluently. The `meta` key is omitted from the output entirely when empty:

```php
$item = (new Item($user, new UserSerializer()))
    ->addMeta('version', 2)
    ->addMeta('requestId', 'abc123');

$envelope = $formatter->format($item);
```

```json
{
  "data": { "id": 1, "name": "Mike", "email": "mike@georgeff.co" },
  "meta": { "version": 2, "requestId": "abc123" }
}
```

## Pagination

### Cursor pagination

Implement `CursorInterface` and attach it to a `Collection` with `setCursor()`:

```php
use Meritum\Serialization\Pagination\CursorInterface;

final class MyCursor implements CursorInterface
{
    public function getPrevious(): ?string { ... }
    public function getNext(): ?string { ... }
    public function getPerPage(): int { ... }
}
```

```php
$collection = (new Collection($users, new UserSerializer()))
    ->setCursor($cursor);

$envelope = $formatter->format($collection);
```

```json
{
  "limit": 25,
  "previous": null,
  "next": "eyJpZCI6MjZ9",
  "data": [...]
}
```

### Offset pagination

Implement `PaginatorInterface` and attach it with `setPaginator()`:

```php
use Meritum\Serialization\Pagination\PaginatorInterface;

final class MyPaginator implements PaginatorInterface
{
    public function getCurrentPage(): int { ... }
    public function getLastPage(): int { ... }
    public function getTotal(): int { ... }
    public function getCount(): int { ... }
    public function getPerPage(): int { ... }
}
```

```php
$collection = (new Collection($users, new UserSerializer()))
    ->setPaginator($paginator);

$envelope = $formatter->format($collection);
```

```json
{
  "total": 100,
  "count": 20,
  "limit": 20,
  "current": 2,
  "last": 5,
  "data": [...]
}
```

`setCursor()` and `setPaginator()` are mutually exclusive — setting one clears the other. Pagination keys always appear before `data` in the output.

A bridge package for `meritum/database` paginators will provide concrete implementations of both interfaces.

## Output strategies

### `DataArrayStrategy` (default)

Items are wrapped under a `data` key. Collections always include a `data` key. This is the default strategy registered by `SerializationModule`.

### `ArrayStrategy`

Items are returned as a bare array with no `data` wrapper. Collections behave the same as `DataArrayStrategy` — the difference only applies to items.

```php
use Meritum\Serialization\Formatter;
use Meritum\Serialization\Strategy\ArrayStrategy;

$formatter = new Formatter(new ArrayStrategy());

$envelope = $formatter->format(new Item($user, new UserSerializer()));
// {"id": 1, "name": "Mike", "email": "mike@georgeff.co"}
```

### Custom strategies

Implement `StrategyInterface` to take full control of the output format:

```php
use Meritum\Serialization\EnvelopeInterface;
use Meritum\Serialization\Envelope;
use Meritum\Serialization\Resource\ItemInterface;
use Meritum\Serialization\Resource\CollectionInterface;
use Meritum\Serialization\Strategy\StrategyInterface;

final class MyStrategy implements StrategyInterface
{
    public function item(ItemInterface $item): EnvelopeInterface
    {
        $data = ['result' => $item->getSerializer()->serialize($item->getData())];

        if ([] !== $item->getMeta()) {
            $data['meta'] = $item->getMeta();
        }

        return new Envelope($data);
    }

    public function collection(CollectionInterface $collection): EnvelopeInterface
    {
        $data = $collection->getPagination();
        $data['results'] = [];

        foreach ($collection->getData() as $model) {
            $data['results'][] = $collection->getSerializer()->serialize($model);
        }

        if ([] !== $collection->getMeta()) {
            $data['meta'] = $collection->getMeta();
        }

        return new Envelope($data);
    }
}
```

## Kernel integration

`SerializationModule` registers `FormatterInterface` in the kernel container, along with a fallback `StrategyInterface` that resolves to `DataArrayStrategy`. To swap in a custom strategy, define `StrategyInterface` before booting, either in your bootstrap or from any module. Your definition replaces the fallback regardless of module order:

```php
use Meritum\Serialization\SerializationModule;
use Meritum\Serialization\Strategy\StrategyInterface;

$kernel->define(StrategyInterface::class, fn () => new MyStrategy());
$kernel->addModule(new SerializationModule());
$kernel->boot();

$formatter = $kernel->getContainer()->get(FormatterInterface::class);
```

To run multiple formatters with different strategies in the same application, bypass the module and define them manually:

```php
use Meritum\Serialization\Formatter;
use Meritum\Serialization\Strategy\ArrayStrategy;
use Meritum\Serialization\Strategy\DataArrayStrategy;

$kernel->define('formatter.default', fn () => new Formatter(new DataArrayStrategy()));
$kernel->define('formatter.bare',    fn () => new Formatter(new ArrayStrategy()));
```

To replace the module's `FormatterInterface` itself, use `$kernel->override()`. Calling `define()` with an id that's already defined throws a `DefinitionException`:

```php
$kernel->override(FormatterInterface::class, fn () => new MyFormatter());
```

## License

MIT
