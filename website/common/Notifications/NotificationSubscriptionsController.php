<?php

namespace Common\Notifications;

use Auth;
use Common\Core\BaseController;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationSubscriptionsController extends BaseController
{
    /**
     * @var Filesystem
     */
    private $fs;
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Filesystem $fs
     * @param Request $request
     */
    public function __construct(
        Filesystem $fs,
        Request $request
    ) {
        $this->fs = $fs;
        $this->request = $request;
    }

    /**
     * @param int $userId
     * @return Response
     */
    public function index($userId)
    {
        $this->authorize('index', [NotificationSubscription::class, $userId]);

        $response = $this->getConfig();
        $subs =  Auth::user()->notificationSubscriptions;
        $response['user_selections'] = $subs;

        return $this->success($response);
    }

    /**
     * @param int $userId
     * @return Response
     */
    public function update($userId)
    {
        $this->authorize('update', [NotificationSubscription::class, $userId]);

        $this->validate($this->request, [
            'selections' => 'present|array',
            'selections.*.notif_id' => 'required|string',
            'selections.*.channels' => 'required|array',
        ]);

        foreach ($this->request->get('selections') as $selection) {
            $subscription = Auth::user()->notificationSubscriptions()->firstOrNew(['notif_id' => $selection['notif_id']]);
            $newChannels = $subscription['channels'];
            // can update state of all channels at once or only a single channel
            foreach ($selection['channels'] as $newChannel => $isSubscribed) {
                $newChannels[$newChannel] = $isSubscribed;
            }
            $subscription->fill(['channels' => $newChannels])->save();
        }

        return $this->success();
    }

    private function getConfig()
    {
        return $this->fs->getRequire(resource_path('defaults/notification-settings.php'));
    }
}
