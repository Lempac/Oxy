<?php

$content = file_get_contents('app/Http/Controllers/HomeController.php');

// We want to favor HEAD (our slugs) but keep the new whiteboard functions from origin/main.
$content = preg_replace('/<<<<<<< HEAD\n(.*?)\n=======\n(.*?)\n>>>>>>> origin\/main/s', '$1', $content);

file_put_contents('app/Http/Controllers/HomeController.php', $content);
