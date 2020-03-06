<?php

namespace Mailchimp\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Mailchimp\Mailchimp\MailchimpApiClient;

class MailchimpShell extends Shell
{
    /**
     * @var MailchimpApiClient
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
            $this->Mailchimp = new MailchimpApiClient($mailchimpConfig);
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
                'default' => null
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
            ->addOption('limit', [
                'help' => 'Number of maximum response items',
                'required' => false,
                'default' => 10
            ])
            ->addOption('offset', [
                'help' => 'Number of maximum response items',
                'required' => false,
                'default' => 0
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
        $listId = $this->getParam('list');
        if (!$listId) {
            $this->abort('Please specify a list using the `--list` option');
        }

        $offset = $this->getParam('offset') ?? 0;
        $limit = $this->getParam('limit') ?? 10;

        $this->info("Requesting subscribers for list " . $listId);
        $subscribers = $this->Mailchimp->getMembers($listId, [
            'count' => $limit,
            'offset' => $offset
        ]);
        if (!$subscribers || !isset($subscribers['members'])) {
            $this->abort("Failed to fetch subscribers");
        }

        $this->info(sprintf("Found %d of %d subscribers", count($subscribers['members']), $subscribers['total_items']));
        foreach ($subscribers['members'] as $s) {
            $mergeFields = $s['merge_fields'];
            $this->out(sprintf("> %s:%s:%s:%s:%s", $s['id'], $s['email_address'], $s['status'], $mergeFields['FNAME'], $mergeFields['LNAME']));
        }
    }

    public function getSubscriber()
    {
        $listId = $this->getParam('list');
        $email = $this->getParam('email');
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
        $listId = $this->getParam('list');
        $email = $this->getParam('email');
        if (!$listId || !$email) {
            $this->abort("ListId or subscriber email missing");
        }

        $this->info(sprintf("Adding subscriber with email `%s`to list `%s`", $email, $listId));
        try {
            $result = $this->Mailchimp->addSubscriber($email, $listId, [
                'merge_fields' => [
                    'FNAME' => $this->getParam('name')
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
        $listId = $this->getParam('list');
        $email = $this->getParam('email');
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
        $listId = $this->getParam('list');
        $email = $this->getParam('email');
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
