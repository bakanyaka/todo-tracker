@setup
    $user = 'admin';

    $group = 'apache';

    $timezone = 'Europe/Moscow';

    $path = '/var/www/todo-tracker.arsenal.plm';
    $current = $path.'/current';

    $repo = 'git@github.com:bakanyaka/todo-tracker.git';
    $branch = isset($branch) ? $branch : 'master';

    $chmods = [
        'storage/logs'
    ];

    $date = new DateTime('now', new DateTimeZone($timezone));
    $release = $path . '/releases/' . $date->format('YmdHis');
@endsetup

@servers(['production' => ['administrator@srv-web02']]);

@task('clone', ['on' => $on])
    mkdir -p {{ $release }}
    git clone --depth 1 -b {{ $branch }} "{{ $repo }}" {{ $release }}
    echo "Repository has been cloned"
@endtask

@task('composer', ['on' => $on])
    cd {{ $release }}
    composer install --no-interaction --no-dev --prefer-dist
    echo "Composer dependencies have been installed"
@endtask

@task('artisan', ['on' => $on])
    cd {{ $release }}
    ln -nfs {{ $path }}/.env .env
    chgrp -h {{ $group }} .env

    php artisan backup:run --only-db
    php artisan config:clear
    php artisan migrate --force
{{--    php artisan migrate --path=database/migrations/live-data-patches --force--}}
    php artisan clear-compiled --env=production
    php artisan optimize --env=production

    echo "Production dependencies have been installed"
@endtask

@task('chmod', ['on' => $on])
    chgrp -R {{ $group }} {{ $release }}
    chmod -R ug+rwx {{ $release }}

    @foreach($chmods as $file)
        chmod -R 775 {{ $release }}/{{ $file }}
        chown -R {{ $user }}:{{ $group }} {{ $release }}/{{ $file }}

        echo "Permissions have been set for {{ $file }}"
    @endforeach

    echo "Permissions have been set"
@endtask

@task('update_symlinks', ['on' => $on])
    ln -nfs {{ $release }} {{ $current }}
    chgrp -h {{ $group }} {{ $current }}
    echo "Symlinks has been set"
@endtask

@task('npm_run_dev')
    cd {{ $release }}
    npm install
    npm run dev
    echo "Javascript modules has been built"
@endtask

@story('release', ['on' => 'production'])
    clone
    composer
    artisan
    npm_run_dev
    chmod
    update_symlinks
@endstory
