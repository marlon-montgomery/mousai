<?php

namespace Common\Settings\Mail;

use Common\Settings\Settings;
use File;
use Google\Service\Gmail\Message;
use Google\Service\Gmail\WatchRequest;
use Google\Service\Gmail\WatchResponse;
use Google_Client;
use Google_Service_Gmail;

class GmailClient
{
    /**
     * @var Google_Service_Gmail
     */
    private $gmail;

    /**
     * @var Google_Client
     */
    private $googleClient;

    public function __construct()
    {
        $this->buildGoogleClient();
    }

    public static function tokenPath(): string
    {
        return storage_path('app/tokens/gmail.json');
    }

    public static function tokenExists(): bool
    {
        return file_exists(self::tokenPath());
    }

    public function sendEmail(string $rawContent): Message
    {
        $encoded = strtr(base64_encode($rawContent), ['+' => '-', '/' => '_']);
        $msg = tap(new Message())->setRaw($encoded);
        return $this->gmail->users_messages->send('me', $msg);
    }

    public function listHistory(int $historyId): array
    {
        $response = $this->gmail->users_history->listUsersHistory('me', [
            'startHistoryId' => $historyId,
        ]);

        $messageIds = collect($response['history'])
            ->map(function ($history) {
                $msg = $history['messagesAdded'][0]['message'] ?? null;
                $labels = $msg['labelIds'] ?? [];

                if ($msg && array_search('SENT', $labels) === false) {
                    return $msg['id'];
                }
            })
            ->filter();

        if ($messageIds->isEmpty()) {
            return [];
        }

        $this->googleClient->setUseBatch(true);
        $batch = $this->gmail->createBatch();

        $messageIds->each(function ($msgId) use ($batch) {
            $request = $this->gmail->users_messages->get('me', $msgId, [
                'format' => 'raw',
            ]);
            $batch->add($request);
        });

        $this->googleClient->setUseBatch(false);

        return array_values($batch->execute());
    }

    public function watch(): WatchResponse
    {
        $payload = new WatchRequest();
        $payload->topicName = app(Settings::class)->get(
            'gmail.incoming.topicName',
        );
        $payload->labelIds = ['UNREAD'];
        $payload->labelFilterAction = 'include';
        return $this->gmail->users->watch('me', $payload);
    }

    private function buildGoogleClient(): void
    {
        $this->googleClient = new Google_Client();
        $this->googleClient->setClientId(config('services.google.client_id'));
        $this->googleClient->setClientSecret(
            config('services.google.client_secret'),
        );

        if (self::tokenExists()) {
            $tokenJson = file_get_contents(self::tokenPath());
            $accessToken = json_decode($tokenJson, true);
            $this->googleClient->setAccessToken($accessToken);
        }

        if ($this->googleClient->isAccessTokenExpired()) {
            $newToken = $this->googleClient->fetchAccessTokenWithRefreshToken(
                $this->googleClient->getRefreshToken(),
            );
            $oldToken = json_decode(File::get(self::tokenPath()), true);
            $mergedToken = array_merge($oldToken, $newToken);
            File::put(self::tokenPath(), json_encode($mergedToken));
        }

        $this->gmail = new Google_Service_Gmail($this->googleClient);
    }
}
