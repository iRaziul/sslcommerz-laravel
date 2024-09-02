<?php

test('has config', function () {
    expect(config('sslcommerz'))
        ->toHaveKeys(['sandbox', 'store', 'route']);
});
