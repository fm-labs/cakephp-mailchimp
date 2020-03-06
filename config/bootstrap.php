<?php

use Cake\Log\Log;

if (!Log::getConfig('mailchimp')) {
    Log::setConfig('mailchimp', [
        'className' => 'Cake\Log\Engine\FileLog',
        'path' => LOGS,
        'file' => 'mailchimp',
        //'levels' => ['notice', 'info', 'debug'],
        'scopes' => ['mailchimp']
    ]);
}