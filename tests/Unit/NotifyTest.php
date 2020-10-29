<?php

namespace Exceedone\Exment\Tests\Unit;

use Illuminate\Support\Facades\Notification;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\NotifyNavbar;
use Exceedone\Exment\Services\NotifyService;
use Exceedone\Exment\Jobs;

class NotifyTest extends UnitTestBase
{
    public function testNotifySlack()
    {
        Notification::fake();
        Notification::assertNothingSent();
    
        $webhook_url = 'https://hooks.slack.com/services/XXXXX/YYYY';
        $subject = 'テスト';
        $body = '本文です';

        $notifiable = NotifyService::notifySlack([
            'webhook_url' => $webhook_url,
            'subject' => $subject,
            'body' => $body,
        ]);

        Notification::assertSentTo($notifiable, Jobs\SlackSendJob::class, 
            function($notification, $channels, $notifiable) use($webhook_url, $subject, $body) {
                return ($notifiable->getWebhookUrl() == $webhook_url) &&
                    ($notifiable->getSubject() == $subject) &&
                    ($notifiable->getBody() == $body);
            });
    }

    public function testNotifyTeams()
    {
        Notification::fake();
        Notification::assertNothingSent();
    
        $webhook_url = 'https://outlook.office.com/webhook/XXXXX/YYYYYY';
        $subject = 'テスト';
        $body = '本文です';

        $notifiable = NotifyService::notifyTeams([
            'webhook_url' => $webhook_url,
            'subject' => $subject,
            'body' => $body,
        ]);

        Notification::assertSentTo($notifiable, Jobs\MicrosoftTeamsJob::class, 
            function($notification, $channels, $notifiable) use($webhook_url, $subject, $body) {
                return ($notifiable->getWebhookUrl() == $webhook_url) &&
                    ($notifiable->getSubject() == $subject) &&
                    ($notifiable->getBody() == $body);
            });
    }

    public function testNotifyNavbar()
    {
        $user = CustomTable::getEloquent('user')->getValueModel()->first();
        $subject = 'テスト';
        $body = '本文です';

        NotifyService::notifyNavbar([
            'subject' => $subject,
            'body' => $body,
            'user' => $user,
        ]);

        $data = NotifyNavbar::withoutGlobalScopes()->orderBy('created_at', 'desc')->first();
        $this->assertEquals(array_get($data, 'notify_subject'), $subject);
        $this->assertEquals(array_get($data, 'notify_body'), $body);
        $this->assertEquals(array_get($data, 'target_user_id'), $user->id);
    }
}
