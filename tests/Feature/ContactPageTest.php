<?php

use function Pest\Laravel\get;

test('contact page can be accessed', function () {
    $response = get(route('contact'));

    $response->assertOk();
    $response->assertViewIs('contact');
});

test('contact page displays email and whatsapp information', function () {
    $response = get(route('contact'));

    $response->assertOk();
    $response->assertSee(config('contact.email'));
    $response->assertSee('WhatsApp', false);
    $response->assertSee('Email', false);
});

test('contact page contains mailto and whatsapp links', function () {
    $response = get(route('contact'));

    $response->assertOk();
    $response->assertSee('mailto:'.config('contact.email'), false);
    $response->assertSee('wa.me', false);
});
