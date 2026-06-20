<?php

namespace App\Console\Commands;

use App\Models\PushSubscription;
use Illuminate\Console\Command;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendDailyNotifications extends Command
{
    protected $signature = 'notify:daily';
    protected $description = 'Send daily progress reminder push notifications to all subscribers';

    public function handle(): void
    {
        $publicKey  = config('services.vapid.public_key');
        $privateKey = config('services.vapid.private_key');
        $subject    = config('services.vapid.subject');

        if (! $publicKey || ! $privateKey) {
            $this->error('VAPID keys not configured. Set VAPID_PUBLIC_KEY and VAPID_PRIVATE_KEY in .env');
            return;
        }

        $push = new WebPush([
            'VAPID' => [
                'subject'    => $subject,
                'publicKey'  => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);

        $subscriptions = PushSubscription::with('user')->get();
        $sent = 0;

        foreach ($subscriptions as $sub) {
            $payload = json_encode([
                'title' => 'Daily check-in reminder',
                'body'  => 'Track today\'s progress — keep your streak alive!',
                'url'   => '/checkins/daily',
            ]);

            $push->queueNotification(
                Subscription::create([
                    'endpoint'   => $sub->endpoint,
                    'publicKey'  => $sub->public_key,
                    'authToken'  => $sub->auth_token,
                ]),
                $payload
            );
            $sent++;
        }

        foreach ($push->flush() as $report) {
            if (! $report->isSuccess()) {
                $this->warn('Failed: ' . $report->getReason());
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getRequest()->getUri()->__toString())->delete();
                }
            }
        }

        $this->info("Sent daily reminder to {$sent} subscriber(s).");
    }
}
