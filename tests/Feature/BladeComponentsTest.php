<?php

use Illuminate\Support\Facades\Blade;

test('the alert component renders without querying removed blade compiler APIs', function () {
    $html = Blade::render('<x-alert style="success">Pass created</x-alert>');

    expect($html)
        ->toContain('role="alert"')
        ->toContain('Pass created');
});
