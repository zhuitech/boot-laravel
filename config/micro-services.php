<?php

return (static function() {
    $services = [
        'auth'          => 'http://ice-auth_default',
        'apps'          => 'http://ice-apps_default',
        'event'         => 'http://ice-event_default',
        'marketing'     => 'http://ice-marketing_default',
        'statistic'     => 'http://ice-statistic_default',
        'member'        => 'http://ice-member_default',

        'rules'         => 'http://ice-rules_default',
        'material'      => 'http://ice-material_default',
        'log'           => 'http://ice-log_default',
        'system'        => 'http://ice-system_default',
        'wechat'        => 'http://ice-wechat_default',
        'sms'           => 'http://ice-sms_default',
        'mail'          => 'http://ice-mail_default',
        'stats'          => 'http://ice-stats_default',
    ];

    foreach ($services as $name => $value) {
        $config = env('SERVICE_' . strtoupper($name), false);
        if ($config) {
            $services[$name] = $config;
        }
    }

    return $services;
})();
