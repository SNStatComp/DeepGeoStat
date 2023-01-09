#!/usr/bin/env sh
ENV_FILENAME=".env"

(test -f $ENV_FILENAME && echo ".env file found for the API" || cp "$ENV_FILENAME.default" $ENV_FILENAME) \
&& docker compose -f docker-compose.dev.yml up -d;

test $? -eq 0 && echo ">> API build successful!" || (echo ">> API build failed!" && exit 1)

