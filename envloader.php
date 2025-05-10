<?php
function loadEnv($path)
{
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove aspas, se existirem
        $value = trim($value, '"\'');

        // Define como variável de ambiente e no $_ENV
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}

