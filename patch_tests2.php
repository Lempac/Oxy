<?php

// Since we changed return response()->json() to return back(), we need to change assertStatus(200) to assertRedirect() / assertStatus(302)
function processFile($file)
{
    $content = file_get_contents($file);
    $content = str_replace("\$response->assertStatus(200);\n    \$response->assertJson(['message' => 'Audio data sent successfully']);", '$response->assertStatus(302);', $content);
    $content = str_replace("\$response->assertStatus(201);\n    \$response->assertJson(['message' => 'Message updated']);", '$response->assertStatus(302);', $content);
    $content = str_replace('$response->assertStatus(200);', '$response->assertStatus(302);', $content);
    file_put_contents($file, $content);
}

processFile('tests/Feature/Api/ChannelControllerTest.php');
processFile('tests/Feature/Api/RoleControllerTest.php');
processFile('tests/Feature/Api/MessageControllerTest.php');
