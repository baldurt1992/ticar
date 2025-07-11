#!/bin/bash

TARGET=$(pwd)
MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-}"

echo "🚀 Ejecutando post-deploy en $TARGET"

# 1. Validar .env
if [ ! -f "$TARGET/.env" ]; then
  echo "❌ No se encontró el archivo .env"
  exit 1
fi

# 2. Extraer credenciales de .env
DB_NAME=$(grep DB_DATABASE "$TARGET/.env" | cut -d '=' -f2 | tr -d '"')
DB_USER=$(grep DB_USERNAME "$TARGET/.env" | cut -d '=' -f2)
DB_PASS=$(grep DB_PASSWORD "$TARGET/.env" | cut -d '=' -f2)

echo "🔧 Creando DB: $DB_NAME y USER: $DB_USER"

# 3. Crear base de datos y usuario
mysql -uadmin -p"$MYSQL_ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -uadmin -p"$MYSQL_ROOT_PASS" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -uadmin -p"$MYSQL_ROOT_PASS" -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -uadmin -p"$MYSQL_ROOT_PASS" -e "FLUSH PRIVILEGES;"

echo "✅ Base de datos creada correctamente"

# 4. Instalar dependencias PHP
echo "📦 Ejecutando composer install"
composer install --no-interaction --prefer-dist --optimize-autoloader

# 5. Generar clave y migrar
echo "🔧 Ejecutando comandos de Laravel"
php artisan key:generate
php artisan config:clear
php artisan config:cache
php artisan migrate --force

# 6. Permisos
echo "🔒 Estableciendo permisos"
chown -R psacln:psacln .
chmod -R 755 .

echo "✅ Proyecto desplegado completamente 🎉"
