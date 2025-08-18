<?php

test('redirects to login page', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login'); // or whatever your login route is
});
