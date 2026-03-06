<?php

declare(strict_types=1);

namespace Tests;

use App\SortedLinkedList;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SortedLinkedListTest extends TestCase
{
    #[DataProvider('provideCollectionState')]
    public function testCollectionStateFromConstructor(
        array $initialValues,
        bool $expectedIsEmpty,
        int $expectedCount,
        array $expectedOrder
    ): void {
        $list = new SortedLinkedList($initialValues);

        self::assertSame($expectedIsEmpty, $list->isEmpty());
        self::assertSame($expectedCount, $list->count());
        self::assertSame($expectedOrder, $list->toArray());
        self::assertSame($expectedOrder, iterator_to_array($list));

        # iterator stability
        self::assertSame($expectedOrder, iterator_to_array($list));
    }

    public static function provideCollectionState(): iterable
    {
        yield 'empty-collection' => [[], true, 0, []];
        yield 'int-collection-sorted' => [[5, 1, 3, 2, 3], false, 5, [1, 2, 3, 3, 5]];
        yield 'string-collection-sorted' => [
            ['pear', 'apple', 'orange', 'apple'],
            false,
            4,
            ['apple', 'apple', 'orange', 'pear'],
        ];
        yield 'string-collection-sorted-mixed-case' => [
            ['banana', 'Apple', 'apple', 'Banana'],
            false,
            4,
            ['Apple', 'Banana', 'apple', 'banana'],
        ];
    }

    #[DataProvider('provideMixedListInvalid')]
    public function testMixedListThrowsException(array $values): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SortedLinkedList($values);
    }

    public static function provideMixedListInvalid(): iterable
    {
        yield 'string-then-int' => [['pear', 4]];
        yield 'int-then-string' => [[1, 'orange']];
        yield 'string-then-int-then-more' => [['pear', 4, 'orange', 1]];
        yield 'int-then-string-then-more' => [[4, 'pear', 1, 'orange']];
    }

    #[DataProvider('provideInsert')]
    public function testInsertIntoSortedList(
        array $input,
        int | string $toInsert,
        array $expectedAfter
    ): void {
        $list = new SortedLinkedList($input);

        $list->insert($toInsert);

        self::assertSame($expectedAfter, $list->toArray());
        self::assertSame($expectedAfter, iterator_to_array($list));
        self::assertSame(count($expectedAfter), $list->count());
        self::assertTrue($list->contains($toInsert));
    }

    public static function provideInsert(): iterable
    {
        yield 'ints-insert-into-empty' => [[], 3, [3]];
        yield 'ints-insert-at-start' => [[2, 4, 6], 1, [1, 2, 4, 6]];
        yield 'ints-insert-in-middle' => [[1, 3, 5], 4, [1, 3, 4, 5]];
        yield 'ints-insert-at-end' => [[1, 3, 5], 7, [1, 3, 5, 7]];
        yield 'ints-insert-duplicate' => [[1, 2, 2, 5], 2, [1, 2, 2, 2, 5]];
        yield 'strings-insert-into-empty' => [[], 'orange', ['orange']];
        yield 'strings-insert-at-start' => [['banana', 'pear'], 'apple', ['apple', 'banana', 'pear']];
        yield 'strings-insert-in-middle' => [['apple', 'pear'], 'banana', ['apple', 'banana', 'pear']];
        yield 'strings-insert-at-end' => [['apple', 'pear'], 'orange', ['apple', 'orange', 'pear']];
        yield 'strings-insert-duplicate' => [
            ['apple', 'banana', 'banana'],
            'banana',
            ['apple', 'banana', 'banana', 'banana'],
        ];
    }

    #[DataProvider('provideInsertInvalidType')]
    public function testInsertWithInvalidTypeThrowsException(
        array $initialValues,
        int | string $invalidValue
    ): void {
        $list = new SortedLinkedList($initialValues);

        $this->expectException(InvalidArgumentException::class);
        $list->insert($invalidValue);
    }

    public static function provideInsertInvalidType(): iterable
    {
        yield 'insert-string-into-int-collection' => [[1, 2, 3], 'apple'];
        yield 'insert-int-into-string-collection' => [['apple', 'banana'], 1];
    }

    #[DataProvider('provideRemove')]
    public function testRemoveFromSortedList(
        array $input,
        int | string $toRemove,
        array $expectedAfter,
        bool $expectedRemoved,
    ): void {
        $list = new SortedLinkedList($input);

        self::assertSame($expectedRemoved, $list->remove($toRemove));
        self::assertSame($expectedAfter, $list->toArray());
        self::assertSame($expectedAfter, iterator_to_array($list));
        self::assertSame($expectedAfter === [], $list->isEmpty());
        self::assertSame(count($expectedAfter), $list->count());
        self::assertFalse($list->contains($toRemove));
    }

    public static function provideRemove(): iterable
    {
        yield 'empty-remove-missing' => [[], 3, [], false];
        yield 'ints-remove-existing-at-start' => [[5, 1, 3, 2], 1, [2, 3, 5], true];
        yield 'ints-remove-existing-in-middle' => [[5, 1, 3, 2], 3, [1, 2, 5], true];
        yield 'ints-remove-existing-at-end' => [[5, 1, 3, 2], 5, [1, 2, 3], true];
        yield 'ints-remove-single-element' => [[2], 2, [], true];
        yield 'ints-remove-missing' => [[5, 1, 3, 2], 4, [1, 2, 3, 5], false];
        yield 'ints-remove-string-type-mismatch' => [[5, 1, 3, 2], '3', [1, 2, 3, 5], false];
        yield 'strings-remove-existing-at-start' => [['pear', 'apple', 'orange'], 'apple', ['orange', 'pear'], true];
        yield 'strings-remove-existing-in-middle' => [['pear', 'apple', 'orange'], 'orange', ['apple', 'pear'], true];
        yield 'strings-remove-existing-at-end' => [['pear', 'apple', 'orange'], 'pear', ['apple', 'orange'], true];
        yield 'strings-remove-single-element' => [['pear'], 'pear', [], true];
        yield 'strings-remove-missing' => [
            ['pear', 'apple', 'orange'],
            'banana',
            ['apple', 'orange', 'pear'],
            false,
        ];
        yield 'strings-remove-int-type-mismatch' => [
            ['pear', 'apple', 'orange'],
            1,
            ['apple', 'orange', 'pear'],
            false,
        ];
        yield 'strings-remove-lowercase-does-not-remove-uppercase' => [
            ['Apple'],
            'apple',
            ['Apple'],
            false,
        ];
        yield 'strings-remove-lowercase-from-case-variant-pair' => [
            ['Apple', 'apple'],
            'apple',
            ['Apple'],
            true,
        ];
    }

    #[DataProvider('provideRemoveDuplicate')]
    public function testRemoveDuplicateFromSortedList(
        array $input,
        int | string $toRemove,
        array $expectedAfter,
        bool $expectedRemoved,
    ): void {
        $list = new SortedLinkedList($input);

        self::assertSame($expectedRemoved, $list->remove($toRemove));
        self::assertSame($expectedAfter, $list->toArray());
        self::assertSame($expectedAfter, iterator_to_array($list));
        self::assertSame(count($expectedAfter), $list->count());
    }

    public static function provideRemoveDuplicate(): iterable
    {
        yield 'ints-remove-first-duplicate-only' => [[2, 2, 1, 2, 4], 2, [1, 2, 2, 4], true];
        yield 'strings-remove-first-duplicate-only' => [
            ['kiwi', 'kiwi', 'pear', 'apple'],
            'kiwi',
            ['apple', 'kiwi', 'pear'],
            true,
        ];
    }

    #[DataProvider('provideContains')]
    public function testContainsInSortedList(
        array $input,
        int | string $searchedValue,
        bool $expected
    ): void {
        $list = new SortedLinkedList($input);

        self::assertSame($expected, $list->contains($searchedValue));
    }

    public static function provideContains(): iterable
    {
        yield 'empty-list-int' => [[], 1, false];
        yield 'empty-list-string' => [[], 'apple', false];
        yield 'ints-existing' => [[5, 1, 3, 2], 3, true];
        yield 'ints-missing' => [[5, 1, 3, 2], 4, false];
        yield 'ints-mismatched-string' => [[5, 1, 3, 2], '3', false];
        yield 'strings-existing' => [['pear', 'apple', 'orange'], 'apple', true];
        yield 'strings-missing' => [['pear', 'apple', 'orange'], 'banana', false];
        yield 'strings-mismatched-int' => [['5', '1', '3', '2'], 1, false];
        yield 'strings-duplicate-existing' => [['kiwi', 'kiwi', 'pear'], 'kiwi', true];
        yield 'strings-case-sensitive-existing-uppercase' => [['Apple'], 'Apple', true];
        yield 'strings-case-sensitive-missing-lowercase' => [['Apple'], 'apple', false];
    }

    #[DataProvider('provideClear')]
    public function testClearResetsListState(array $initialValues): void
    {
        $list = new SortedLinkedList($initialValues);

        $list->clear();

        self::assertTrue($list->isEmpty());
        self::assertSame(0, $list->count());
        self::assertSame([], $list->toArray());
        self::assertSame([], iterator_to_array($list));
    }

    public static function provideClear(): iterable
    {
        yield 'clear-empty-list' => [[]];
        yield 'clear-non-empty-int-list' => [[5, 1, 3]];
        yield 'clear-non-empty-string-list' => [['pear', 'apple']];
    }

    #[DataProvider('provideClearTypeReset')]
    public function testClearResetsAcceptedType(
        array $initialValues,
        int | string $valueAfterClear,
        array $expectedAfterInsert
    ): void {
        $list = new SortedLinkedList($initialValues);

        $list->clear();
        $list->insert($valueAfterClear);

        self::assertSame($expectedAfterInsert, $list->toArray());
        self::assertTrue($list->contains($valueAfterClear));
        self::assertSame(1, $list->count());
    }

    public static function provideClearTypeReset(): iterable
    {
        yield 'int-to-string-after-clear' => [[3, 1, 2], 'apple', ['apple']];
        yield 'string-to-int-after-clear' => [['pear', 'apple'], 7, [7]];
    }

    #[DataProvider('provideClone')]
    public function testCloneCreatesIndependentList(
        array $original,
        int | string $insertIntoOriginal,
        int | string $insertIntoClone,
        array $expectedOriginal,
        array $expectedClone
    ): void {
        $original = new SortedLinkedList($original);
        $clone = clone $original;

        $original->insert($insertIntoOriginal);
        $clone->insert($insertIntoClone);

        self::assertSame($expectedOriginal, $original->toArray());
        self::assertSame($expectedClone, $clone->toArray());
        self::assertNotSame($original->toArray(), $clone->toArray());
    }

    public static function provideClone(): iterable
    {
        yield 'clone-empty-list' => [[], 1, 'apple', [1], ['apple']];
        yield 'clone-int-list' => [[5, 1, 3], 2, 4, [1, 2, 3, 5], [1, 3, 4, 5]];
        yield 'clone-string-list' => [
            ['pear', 'apple', 'orange'],
            'banana',
            'kiwi',
            ['apple', 'banana', 'orange', 'pear'],
            ['apple', 'kiwi', 'orange', 'pear'],
        ];
    }
}
