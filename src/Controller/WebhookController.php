<?php

namespace Mailchimp\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Mailchimp\Event\MailchimpWebhookEvent;

class WebhookController extends Controller
{
    public function beforeFilter(Event $event)
    {
        $this->viewBuilder()->setClassName('Json');
    }

    public function process()
    {
        $error = null;

        try {
            $event = $this->getEventManager()->dispatch(new MailchimpWebhookEvent($this->request->getData()));
            $success = true;
        } catch (\Exception $ex) {
            $success = false;
            $error = $ex->getMessage();
        } finally {
            $request = [
                'data' => $this->request->getData(),
                'ip' => $this->request->clientIp(),
                //'input' => $this->request->input(),
            ];

            $file = TMP . 'mailchimp_' . date("Y-m-d-H-i-s") . '.txt';
            $written = @file_put_contents($file, json_encode($request, JSON_PRETTY_PRINT));

            $success = ($written > 0);
        }

        $result = [
            'success' => $success,
            'error' => $error,
        ];
        $this->set('result', $result);
        $this->set('_serialize', 'result');
    }
}
