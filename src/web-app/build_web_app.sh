#!/usr/bin/env sh
DOCKER_COMPOSE_FILE="docker-compose.dev.yml"
DOCKER_INSTALLER_CONTAINER_NAME="web-app-installer"
DOCKER_WEB_SERVER_CONTAINER_NAME="web-app-serve-1"
ENV_FILENAME=".env"

case $@ in
  -f | --force) docker system prune -a;;
esac

setup_env_file () {
  (test -f $ENV_FILENAME && ".env file found" || cp "$ENV_FILENAME.default" $ENV_FILENAME) \
  && echo ">> Successfully set up the $ENV_FILENAME file."\
  || (echo ">> Failed to set up the $ENV_FILENAME file. $ENV_FILE.default not found." && exit 1)
}

(test -f $ENV_FILENAME && echo ">> .env file found. Make sure the APP_KEY value is correct." || setup_env_file) \
&& docker compose -f $DOCKER_COMPOSE_FILE up installer -d \
&& docker exec $DOCKER_INSTALLER_CONTAINER_NAME composer install \
&& docker exec $DOCKER_INSTALLER_CONTAINER_NAME npm install \
&& docker compose -f $DOCKER_COMPOSE_FILE --profile installer down \
&& docker compose -f $DOCKER_COMPOSE_FILE up -d \
&& docker exec --user 0 $DOCKER_WEB_SERVER_CONTAINER_NAME /bin/bash -c "chown -R www-data:www-data /var/www" \
&& docker exec $DOCKER_WEB_SERVER_CONTAINER_NAME /bin/bash -c "php artisan key:generate" \
&& docker exec $DOCKER_WEB_SERVER_CONTAINER_NAME php artisan migrate --seed \
&& echo "Build successful!\n" \
|| (echo "Build failed...\n" && return 1)
