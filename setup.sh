#!/usr/bin/env sh
PROJECT_NAME="DeepGeoStat"
API_BUILD_FOLDER="experiments-api"
API_BUILD_FILE="build_api.sh"
WEB_APP_BUILD_FOLDER="web-app"
WEB_APP_BUILD_FILE="build_web_app.sh"
WEB_APP_BUILD_FILE_ARGS=""

PROJECT_ROOT_FOLDER=$PWD

case $@ in 
  -f | --force) WEB_APP_BUILD_FILE_ARGS="$WEB_APP_BUILD_FILE_ARGS -f";;
esac

build_web_app() {
  cd "$PROJECT_ROOT_FOLDER/$WEB_APP_BUILD_FOLDER" && sh $WEB_APP_BUILD_FILE $WEB_APP_BUILD_FILE_ARGS;
  BUILD_SUCCESS=$?;
  cd $PROJECT_ROOT_FOLDER; 
  test $BUILD_SUCCESS -eq 0 && echo ">> Web-app built successfully!\n" || (echo ">> Failed to build the webapp...\n" && return 1)
}

build_api() {
  cd "$PROJECT_ROOT_FOLDER/$API_BUILD_FOLDER" && sh $API_BUILD_FILE;
  BUILD_SUCCESS=$?;
  cd $PROJECT_ROOT_FOLDER;
  test $BUILD_SUCCESS -eq 0 && echo ">> API built successfully!\n" || (echo ">> Failed to build the API...\n" && return 1)
}

# Check if the build folders exist
test -e $WEB_APP_BUILD_FOLDER || (echo ">> $WEB_APP_BUILD_FOLDER not found. Aborting..."; exit 1)
test -e $API_BUILD_FOLDER || (echo ">> $API_BUILD_FOLDER not found. Aborting..."; exit 1)

echo "Starting to set up the $PROJECT_NAME project."
build_web_app && build_api && echo ">> Build finished!\n" || echo ">> Failed to build the project...\n"
