# Start

## Manual installation process 

If installation went without any errors then these manual steps were already completed

### Install dependencies
    composer install

    npm install

### Initialize env 

    cp .env.example .env

    php artisan key:generate

### Initialize database

#### For Unix

    touch database/database.sqlite

#### For windows (in terminal I have powershell)

    New-Item -Path .\database\database.sqlite -ItemType File

#### Populate database

    php artisan migrate

    php artisan db:seed

## Connect Google Oauth 

### Configure Google Cloud console
Create project and Oauth 2.0 credentials in https://console.cloud.google.com/apis/credentials

Put http://localhost:8000/auth/google/callback in Authorized redirect URIs

And http://localhost:8000 in Authorized JavaScript origins

Change these URLs appropriately when rolling for production

### Change .env

in .env set these fields to values from Google Cloud 

GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret

You don't need to change 

GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

if you haven't changed it in previous step





