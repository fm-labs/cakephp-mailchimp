<?php

namespace Mailchimp\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Mailchimp\Mailchimp\MailchimpClient;

class MailchimpShell extends Shell
{
    /**
     * @var MailchimpClient
     */
    public $Mailchimp;

    public function initialize()
    {
        parent::initialize();

        try {
            $mailchimpConfig = Configure::read('Mailchimp');
            if (!$mailchimpConfig) {
                throw new \RuntimeException('Mailchimp configuration not found');
            }
            $this->Mailchimp = new MailchimpClient($mailchimpConfig);
        } catch(\Exception $ex) {
            $this->abort($ex->getMessage());
        }
    }

    protected function _welcome(){}

    public function getOptionParser()
    {
        return parent::getOptionParser()
            ->addSubcommand('lists', [
                'help' => 'List all mailchimp lists'
            ])
            ->addSubcommand('subscribers', [
                'help' => 'List all subscribers for a list'
            ])
            ->addSubcommand('getSubscriber', [
                'help' => 'Add subscriber to list'
            ])
            ->addSubcommand('subscribe', [
                'help' => 'Add subscriber to list'
            ])
            ->addSubcommand('unsubscribe', [
                'help' => 'Unsubscribe subscriber to list'
            ])
            ->addSubcommand('removeSubscriber', [
                'help' => 'Remove subscriber from list'
            ])
            ->addOption('list', [
                'help' => 'Mailchimp list ID',
                'required' => false,
                'default' => '5cd57aa1c7' //@TODO Remove this debug default value
            ])
            ->addOption('email', [
                'help' => 'Subscriber email',
                'required' => false,
                'default' => 'flohax@yahoo.de' //@TODO Remove this debug default value
            ])
            ->addOption('name', [
                'help' => 'Subscriber name',
                'required' => false,
                'default' => 'Test Subscriber' //@TODO Remove this debug default value
            ])
            ->addOption('status', [
                'help' => 'Subscriber status',
                'required' => false,
                'default' => 'subscribed' //@TODO Remove this debug default value
            ])
            ;
    }

    public function lists()
    {
        $lists = $this->Mailchimp->getLists();
        if (!$lists || !isset($lists['lists'])) {
            $this->abort("Failed to fetch lists");
        }

        $this->info(sprintf("Found %d lists", count($lists['lists'])));
        foreach ($lists['lists'] as $list) {
            $this->out(sprintf("%s:%s", $list['id'], $list['name']));
        }
    }

    public function subscribers()
    {
        $listId = $this->param('list');
        if (!$listId) {
            $this->abort('Please specify a list using the `--list` option');
        }

        $this->info("Requesting subscribers for list " . $listId);
        $subscribers = $this->Mailchimp->getSubscribers($listId);
        if (!$subscribers || !isset($subscribers['members'])) {
            $this->abort("Failed to fetch subscribers");
        }

        $this->info(sprintf("Found %d subscribers", count($subscribers['members'])));
        foreach ($subscribers['members'] as $s) {
            $this->out(sprintf("%s:%s:%s", $s['id'], $s['email_address'], $s['status']));
        }
    }

    public function getSubscriber()
    {
        $listId = $this->param('list');
        $email = $this->param('email');
        if (!$listId || !$email) {
            $this->abort("ListId or subscriber email missing");
        }

        $this->info(sprintf("Fetching subscriber with email `%s`to list `%s`", $email, $listId));
        try {
            $result = $this->Mailchimp->getSubscriber($email, $listId);
            debug($result);
        } catch(\Mailchimp\Mailchimp\MailchimpException $ex)  {
            $this->err('[' . $ex->getCode() . '] ' . $ex->getMessage());
            debug($ex->getResult());
        } catch(\Exception $ex) {
            $this->err($ex->getMessage());
        }
    }

    public function subscribe()
    {
        $listId = $this->param('list');
        $email = $this->param('email');
        if (!$listId || !$email) {
            $this->abort("ListId or subscriber email missing");
        }

        $this->info(sprintf("Adding subscriber with email `%s`to list `%s`", $email, $listId));
        try {
            $result = $this->Mailchimp->addSubscriber($email, $listId, [
                'merge_fields' => [
                    'FNAME' => $this->param('name')
                ]
            ]);
            debug($result);
        } catch(\Mailchimp\Mailchimp\MailchimpException $ex)  {
            $this->err('[' . $ex->getCode() . '] ' . $ex->getMessage());
            debug($ex->getResult());
        } catch(\Exception $ex) {
            $this->err($ex->getMessage());
        }
    }

    public function unsubscribe()
    {
        $listId = $this->param('list');
        $email = $this->param('email');
        if (!$listId || !$email) {
            $this->abort("ListId or subscriber email missing");
        }

        $this->info(sprintf("Adding subscriber with email `%s`to list `%s`", $email, $listId));
        try {
            $result = $this->Mailchimp->unsubscribeSubscriber($email, $listId);
            debug($result);
        } catch(\Mailchimp\Mailchimp\MailchimpException $ex)  {
            $this->err('[' . $ex->getCode() . '] ' . $ex->getMessage());
        } catch(\Exception $ex) {
            $this->err($ex->getMessage());
        }
    }

    public function removeSubscriber()
    {
        $listId = $this->param('list');
        $email = $this->param('email');
        if (!$listId || !$email) {
            $this->abort("ListId or subscriber email missing");
        }

        $this->info(sprintf("Adding subscriber with email `%s`to list `%s`", $email, $listId));
        try {
            $result = $this->Mailchimp->removeSubscriber($email, $listId);
            debug($result);
        } catch(\Mailchimp\Mailchimp\MailchimpException $ex)  {
            $this->err('[' . $ex->getCode() . '] ' . $ex->getMessage());
        } catch(\Exception $ex) {
            $this->err($ex->getMessage());
        }
    }
}
