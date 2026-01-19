Requirements: 
    running docker instance

1. Build containers 
run "npm run directus:build", if you have not doen so in installation process

2. Start container
run "npm run directus:start"

3. Login to Directus
go to "http://localhost:8055/admin/" and login with credentials specified in docker-compose.yml in this directory (you can and should change them)

4. Create access key 
Go to Admin User profile (bottom on left sidebar) and scroll down to Admin Options section. On Token input press "+" and copy the key to the DIRECTUS_TOKEN to the root .env of the repository. 


5. 


## Helper controllers
I have included Controllers, Models and Service that are useful if you want to use Directus as CMS. 
- DirectusModel - required for when creating models in directus, as it uses different convention for timestamp columns
- DynamicPageController - fetches singleton tables taht represent pages from Directus matcing by path. Requires connection in web.php 

Route::get('/{vue_capture?}', [DynamicPageController::class, 'show'])
    ->where('vue_capture',
    '^(?!\.well-known)(?!robots\.txt$)(?!favicon\.ico$)[\/\w\.-]*$'
);

- DIrectusWebhookController - first setup flow in directus that activates on table changes and send request to this route in this controller. Then when it will, cache will be updated

- DirectusService - main cache place ans fetching data from directus abstraction 