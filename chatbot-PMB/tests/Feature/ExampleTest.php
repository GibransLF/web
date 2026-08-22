<?php

test('returns a successful response for home route with hero image and external buttons', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('images/stmik.jpg')
        ->assertSee('https://pmb.stmik-bandung.ac.id/', false)
        ->assertSee('https://pmb.stmik-bandung.ac.id/faq', false)
        ->assertSee('https://pmb.stmik-bandung.ac.id/pricing', false)
        ->assertSee('https://pmb.stmik-bandung.ac.id/contact', false)
        ->assertDontSee('Kembali ke Home');
});

test('returns successful response for chat route with home button', function () {
    $response = $this->get(route('chat'));

    $response->assertOk()
        ->assertSee('Kembali ke Home');
});
