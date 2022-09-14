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
echo "# 1/6 Instalacja"
rm /var/www/html/config/pentacomp_main.settings.yml;
cd /var/www/html;
composer install -n;
./bin/drush sql-dump --result-file=/var/www/html/orginal_data/dump.sql --yes;
./bin/drush sql:drop --yes;
composer install -n
echo "# 2/6 Konfiguracja"
echo "333"."--db-url=$DATABASE_DRIVER://$DATABASE_USER:$DATABASE_PASSWORD@$DATABASE_HOST:$DATABASE_PORT/$DATABASE_NAME";
./bin/drush si --site-name="ANKIETY UZP" --db-url=$DATABASE_DRIVER://$DATABASE_USER:$DATABASE_PASSWORD@$DATABASE_HOST:$DATABASE_PORT/$DATABASE_NAME --yes ;
# ./bin/drush si --site-name="ANKIETY UZP" --db-url=mysql://root:qwerty12345@pent_quest_db:3306/pent_quest_db --yes ;
./bin/drush entity:delete shortcut_set
./bin/drush sset system.maintenance_mode TRUE;
./bin/drush cset system.site _core.default_config_hash $DRUPAL_CONFIG_HASH -y;
./bin/drush cset system.site uuid $DRUPAL_SYSTEM_UUID -y;
./bin/drush upwd admin admin;
./bin/drush pm:enable migrate migrate_drupal migrate_drupal_ui migrate_json_example migrate_plus migrate_source_csv migrate_tools pentacomp_main
./bin/drush -y config:import sync;
#./bin/drush deploy --yes;
./bin/drush cr;
echo "# 3/3 Import danych";
./bin/drush migrate:import --group=pentacomp_import;
echo "# 4/6 Pobieranie bibliotek dla webform";
#./bin/drush webform:libraries:download --yes;
echo "# 5/6 Pobieranie tłumaczeń";
./bin/drush locale:update; 
echo "# 6/6 KONIEC BUDOWY";
./bin/drush sset system.maintenance_mode FALSE;
./bin/drush cr;

