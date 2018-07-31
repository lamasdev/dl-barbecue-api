cp .env.example .env;
composer install;
php artisan migrate:fresh;
php artisan key:generate;
php artisan passport:install --force;
