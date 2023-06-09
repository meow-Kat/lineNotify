<?php

namespace Gcreate\LineNotify;

use Illuminate\Support\ServiceProvider;

class LineNotifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 如果 root 有 config 設定檔就合併，沒有就產生 config 設定檔，來源在 src/config
        $this->mergeConfigFrom( __DIR__ . '/config/line-notify.php', 'line-notify');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // 發佈 config 設定檔
        // php artisan vendor:publish --tag=line-notify-config
        $source = realpath($raw = __DIR__ . '/config/line-notify.php') ?: $raw;
        $this->publishes([
            $source => config_path('line-notify.php'),
        ],'line-notify-config');

        // 發佈 migration
        // php artisan vendor:publish --tag=line-notify-migration
        $timestamp = date('Y_m_d_His');
        $this->publishes([
            __DIR__.'/database/migrations/add_line_token_column_and_line_state_column_to_users_table.php' => database_path('migrations/'.$timestamp.'_add_line_token_column_and_line_state_token_to_users_table.php'),
        ],'line-notify-migration');

    }
}