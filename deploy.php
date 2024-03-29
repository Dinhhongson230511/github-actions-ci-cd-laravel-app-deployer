<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config
set('repository', 'git@github.com:Dinhhongson230511/github-actions-ci-cd-laravel-app-deployer.git');
set('project_path', '/var/www/html/laravel-app/github-actions-ci-cd-laravel-app-deployer');
set('keep_releases', 2);
add('shared_files', ['.env']);
add('shared_dirs', []);
add('writable_dirs', []);

set('slack_push_done', 'curl -X POST --data-urlencode "payload={\"channel\": \"#your_channel\", \"username\": \"Bot\", \"text\": \"@channel [{{ server_name }}] Server {{ server_url }} has been successfully deployed!\", \"icon_emoji\": \":ghost:\"}"');

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
    ->set('server_name', 'app.dev - $APP_NAME')
    ->set('server_url', '$APP_URL')
    ->set('branch', 'master')
    ->set('deploy_path', '{{ project_path }}');

// Hooks customize task for laravel recipes
after('deploy:update_code', 'npm:run');
after('deploy:publish', 'push:slack:done');

after('deploy:failed', 'deploy:unlock');
