<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config
set('repository', 'git@github.com:Dinhhongson230511/github-actions-ci-cd-laravel-app-deployer.git');
set('keep_releases', 2);
add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

set('dotenv', '{{current_path}}/.env');
set('slack_push_done', 'curl -X POST --data-urlencode "payload={\"channel\": \"#retty-inshokuten-yoyaku\", \"username\": \"Bot\", \"text\": \"@channel [{{ server_name }}] Server {{ server_url }} has been successfully deployed!\", \"icon_emoji\": \":ghost:\"}"');

// Custom Tasks
task('npm:run', function () {
    run('cd {{release_path}} \
        && export NVM_DIR="$HOME/.nvm" \
        && [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" \
        && npm install && npm run build');
});
task('push:slack:done', function () {
    run('{{slack_push_done}} $SLACK_PUSH_CHANNEL');
});

task('deploy:vendors', function () {
    run('cd {{release_path}} && composer install');
});

// Hosts
host('app.dev')
    ->set('server_name', 'retty.dev - $APP_NAME')
    ->set('server_url', '$APP_URL')
    ->set('branch', 'master')
    ->set('deploy_path', '/var/www/html/laravel-app/github-actions-ci-cd-laravel-app-deployer');

// host('retty.prd')
//     ->set('server_name', 'retty.prd - $APP_NAME')
//     ->set('server_url', '$APP_URL')
//     ->set('branch', 'prd')
//     ->set('deploy_path', '/var/www/app/be');

// Hooks customize task for laravel recipes
after('deploy:update_code', 'npm:run');
after('deploy:publish', 'push:slack:done');

after('deploy:failed', 'deploy:unlock');
