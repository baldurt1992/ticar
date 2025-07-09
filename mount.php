<?php

chdir(__DIR__);
echo "⏳ Inicializando Laravel...\n";

// Ejecutar Composer
passthru('~/.phpenv/shims/composer install --no-interaction --prefer-dist');

// Comandos Artisan
passthru('php artisan migrate --force');
passthru('php artisan db:seed --force');
passthru('php artisan config:cache');

http_response_code(200);
echo "✔️ Laravel desplegado y listo\n";
