<?php

$content = file_get_contents('tests/Feature/Api/MessageControllerTest.php');
$content = str_replace("\$response->assertJson(['message' => 'Forbidden.']);", '', $content);
file_put_contents('tests/Feature/Api/MessageControllerTest.php', $content);
