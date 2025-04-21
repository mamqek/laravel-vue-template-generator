# Start

## Install dependencies
    composer install

    npm install

## Initialize env 

    cp .env.example .env

    php artisan key:generate

## Initialize database

### For Unix

    touch database/database.sqlite

### For windows (in terminal I have powershell)

    New-Item -Path .\database\database.sqlite -ItemType File

## Populate database

    php artisan migrate

    php artisan db:seed


