<?php
if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$pwd = null;
if (isset($argv[1]) && $argv[1] !== '') {
    $pwd = $argv[1];
} else {
    // prompt for password if not provided as argument
    $pwd = trim(readline("Password: "));
}

if ($pwd === '') {
    fwrite(STDERR, "No password provided.\n");
    exit(1);
}

echo password_hash($pwd, PASSWORD_DEFAULT) . PHP_EOL;
?>

