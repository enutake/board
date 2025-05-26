#!/usr/bin/env bash

UNAMEOUT="$(uname -s)"

# Verify operating system is supported...
case "${UNAMEOUT}" in
    Linux*)             MACHINE=linux;;
    Darwin*)            MACHINE=mac;;
    *)                  MACHINE="UNKNOWN"
esac

if [ "$MACHINE" == "UNKNOWN" ]; then
    echo "Unsupported operating system [$(uname -s)]. Laravel Sail supports macOS, Linux, and Windows (WSL2)." >&2

    exit 1
fi

# Define environment variables...
export APP_PORT=${APP_PORT:-80}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

if [ "$#" -gt 0 ]; then
    # Proxy the "help" command to the application...
    if [ "$1" == "help" ] || [ "$1" == "-h" ] || [ "$1" == "-help" ] || [ "$1" == "--help" ]; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            php artisan "$@"

    # Proxy Composer commands to the "composer" binary on the application container...
    elif [ "$1" == "composer" ]; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            composer "$@"

    # Proxy Artisan commands to the "artisan" binary on the application container...
    elif [ "$1" == "artisan" ] || [ "$1" == "art" ]; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            php artisan "$@"

    # Proxy the "test" command to the "php artisan test" Artisan command...
    elif [ "$1" == "test" ]; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            php artisan test "$@"

    # Proxy the "phpunit" command to "php vendor/bin/phpunit"...
    elif [ "$1" == "phpunit" ]; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            php vendor/bin/phpunit "$@"

    # Initiate a Laravel Tinker session within the application container...
    elif [ "$1" == "tinker" ] ; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            php artisan tinker

    # Proxy Node commands to the "node" binary on the application container...
    elif [ "$1" == "node" ]; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            node "$@"

    # Proxy NPM commands to the "npm" binary on the application container...
    elif [ "$1" == "npm" ]; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            npm "$@"

    # Proxy NPX commands to the "npx" binary on the application container...
    elif [ "$1" == "npx" ]; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            npx "$@"

    # Proxy YARN commands to the "yarn" binary on the application container...
    elif [ "$1" == "yarn" ]; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            yarn "$@"

    # Initiate a Bash shell within the application container...
    elif [ "$1" == "shell" ] || [ "$1" == "bash" ]; then
        shift 1

        docker-compose exec \
            -u sail \
            laravel.test \
            bash "$@"

    # Initiate a root user Bash shell within the application container...
    elif [ "$1" == "root-shell" ] || [ "$1" == "root" ]; then
        shift 1

        docker-compose exec \
            laravel.test \
            bash "$@"

    # Pass unknown commands to the "docker-compose" binary...
    else
        docker-compose "$@"
    fi
else
    docker-compose ps
fi