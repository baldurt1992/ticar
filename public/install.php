<?php
set_time_limit(300);

passthru("composer install");
passthru("php artisan key:generate");
passthru("php artisan migrate --force");
passthru("php artisan db:seed --force");
passthru("php artisan storage:link");

echo "✅ Proyecto desplegado correctamente";
