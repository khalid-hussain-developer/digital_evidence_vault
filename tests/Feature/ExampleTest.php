<?php

it('redirects guests at the root URL to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
