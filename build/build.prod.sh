echo "##########################################"
echo "# PARAMETRY BUILD "
echo "##########################################"
echo "# PARAMETRY BUILD "
echo "# DATABASE_DRIVER: $DATABASE_DRIVER";
echo "# DATABASE_USER: $DATABASE_USER";
echo "# DATABASE_PASSWORD: $DATABASE_PASSWORD";
echo "# DATABASE_HOST: $DATABASE_HOST";
echo "# DATABASE_PORT: $DATABASE_PORT";
echo "# DATABASE_NAME: $DATABASE_NAME";
echo "# DRUPAL_CONFIG_HASH: $DRUPAL_CONFIG_HASH";
echo "# DRUPAL_SYSTEM_UUID: $DRUPAL_SYSTEM_UUID";
echo "# 1/4 Instalacja"
cd /var/www/html;
composer install;
./bin/drush sset system.maintenance_mode TRUE;
./bin/drush cr;
echo "# 2/4 Aktualizacja";
./bin/drush updb -y
echo "# 3/4 Pobieranie tłumaczeń";
./bin/drush locale:update; 
echo "# 4/4 KONIEC BUDOWY";
./bin/drush cr;
./bin/drush sset system.maintenance_mode FALSE;

