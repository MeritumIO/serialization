<?php

namespace Meritum\Serialization\Resource;

use Meritum\Serialization\SerializerInterface;
use Meritum\Serialization\Pagination\CursorInterface;
use Meritum\Serialization\Pagination\PaginatorInterface;

final class Collection implements CollectionInterface
{
    use MetaTrait;

    private ?CursorInterface $cursor = null;

    private ?PaginatorInterface $paginator = null;

    /**
     * @param iterable<mixed> $data
     */
    public function __construct(
        private readonly iterable $data,
        private readonly SerializerInterface $serializer
    ) {}

    public function getData(): iterable
    {
        return $this->data;
    }

    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    public function getPagination(): array
    {
        $data = [];

        if (null !== $this->cursor) {
            $data['limit']    = $this->cursor->getPerPage();
            $data['previous'] = $this->cursor->getPrevious();
            $data['next']     = $this->cursor->getNext();
        }

        if (null !== $this->paginator) {
            $data['total'] = $this->paginator->getTotal();
            $data['count'] = $this->paginator->getCount();
            $data['limit'] = $this->paginator->getPerPage();
            $data['current'] = $this->paginator->getCurrentPage();
            $data['last']    = $this->paginator->getLastPage();
        }

        return $data;
    }

    public function setCursor(CursorInterface $cursor): static
    {
        $this->paginator = null;

        $this->cursor = $cursor;

        return $this;
    }

    public function setPaginator(PaginatorInterface $paginator): static
    {
        $this->cursor = null;

        $this->paginator = $paginator;

        return $this;
    }
}
