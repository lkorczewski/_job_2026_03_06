<?php

declare(strict_types=1);

namespace App;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, int | string>
 */
final class SortedLinkedList implements Countable, IteratorAggregate
{
    private ?ListNode $head = null;
    /** @var int<0, max> */
    private int $size = 0;
    private ?string $valueType = null;

    /**
     * @param iterable<int | string> $values
     */
    public function __construct(iterable $values = [])
    {
        foreach ($values as $value) {
            $this->insert($value);
        }
    }

    public function __clone(): void
    {
        if ($this->head === null) {
            return;
        }

        $source = $this->head;
        $this->head = new ListNode($source->value);
        $target = $this->head;
        $source = $source->next;

        while ($source !== null) {
            $target->next = new ListNode($source->value);
            $target = $target->next;
            $source = $source->next;
        }
    }

    public function insert(int | string $value): void
    {
        $this->assertValueType($value);

        $newNode = new ListNode($value);
        $previous = null;
        $current = $this->head;

        while ($current !== null && $this->compareValues($current->value, $value) <= 0) {
            $previous = $current;
            $current = $current->next;
        }

        $newNode->next = $current;
        if ($previous === null) {
            $this->head = $newNode;
        } else {
            $previous->next = $newNode;
        }

        $this->size++;
    }

    public function remove(int | string $value): bool
    {
        if (gettype($value) !== $this->valueType) {
            return false;
        }

        $previous = null;
        $current = $this->head;

        while ($current !== null && $this->compareValues($current->value, $value) < 0) {
            $previous = $current;
            $current = $current->next;
        }

        if ($current !== null && $current->value === $value) {
            if ($previous === null) {
                $this->head = $current->next;
            } else {
                $previous->next = $current->next;
            }

            $this->size--;
            if ($this->size === 0) {
                $this->valueType = null;
            }

            return true;
        }

        return false;
    }

    public function contains(int | string $value): bool
    {
        if ($this->valueType !== null && gettype($value) !== $this->valueType) {
            return false;
        }

        $current = $this->head;

        while ($current !== null && $this->compareValues($current->value, $value) < 0) {
            $current = $current->next;
        }

        if ($current !== null && $current->value === $value) {
            return true;
        }

        return false;
    }

    public function clear(): void
    {
        $this->head = null;
        $this->size = 0;
        $this->valueType = null;
    }

    /**
     * @return list<int | string>
     */
    public function toArray(): array
    {
        $values = [];
        $current = $this->head;

        while ($current !== null) {
            $values[] = $current->value;
            $current = $current->next;
        }

        return $values;
    }

    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    # Countable interface

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return $this->size;
    }

    # IteratorAggregate interface

    public function getIterator(): Traversable
    {
        $current = $this->head;

        while ($current !== null) {
            yield $current->value;
            $current = $current->next;
        }
    }

    private function compareValues(int | string $left, int | string $right): int
    {
        return $left <=> $right;
    }

    private function assertValueType(int | string $value): void
    {
        $type = gettype($value);

        if ($this->valueType === null) {
            $this->valueType = $type;

            return;
        }

        if ($type !== $this->valueType) {
            throw new InvalidArgumentException(
                sprintf('This list accepts only %s values.', $this->valueType)
            );
        }
    }
}
