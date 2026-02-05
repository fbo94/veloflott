<?php

declare(strict_types=1);

test('example unit test', function () {
    expect(true)->toBeTrue();
});

test('basic math operations', function () {
    expect(1 + 1)->toBe(2);
    expect(10 * 5)->toBe(50);
});

test('strings can be concatenated', function () {
    $hello = 'Hello';
    $world = 'World';

    expect($hello . ' ' . $world)->toBe('Hello World');
});
