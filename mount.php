<?php

chdir(__DIR__);
echo "<pre>⏳ Inicializando Laravel...\n";

// Composer desde ruta absoluta en Plesk
$composer = '/usr/local/bin/composer';

// 1. Instalar dependencias
echo "📦 Ejecutando composer install...\n";
passthru("$composer install --no-interaction --prefer-dist");

// 2. Generar APP_KEY si no existe
$env = file_get_contents('.env');
if (strpos($env, 'APP_KEY=') !== false && strlen(trim(explode('APP_KEY=', $env)[1])) < 10) {
    echo "\n🔐 Generando APP_KEY...\n";
    passthru("php artisan key:generate");
}

// 3. Migrar base de datos
echo "\n🧩 Migrando base de datos...\n";
passthru("php artisan migrate --force");

// 4. Ejecutar seeders
echo "\n🌱 Ejecutando seeders...\n";
passthru("php artisan db:seed --force");

// 5. Cachear configuración
echo "\n🧹 Cacheando config...\n";
passthru("php artisan config:cache");

// 6. Confirmar
http_response_code(200);
echo "\n✅ Laravel desplegado y listo</pre>\n";
