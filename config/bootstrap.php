<?php

use Cake\Log\Log;

if (!Log::config('mailchimp')) {
    Log::config('mailchimp', [
        'className' => 'Cake\Log\Engine\FileLog',
        'path' => LOGS,
        'file' => 'mailchimp',
        //'levels' => ['notice', 'info', 'debug'],
        'scopes' => ['mailchimp']
    ]);
}