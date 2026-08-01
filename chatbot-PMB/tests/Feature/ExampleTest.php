<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('returns successful response for chat route', function () {
    $response = $this->get(route('chat'));

    $response->assertOk();
});
