<?php

namespace Common\Notifications;

use Auth;
use Carbon\Carbon;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends BaseController
{
    /**
     * @var DatabaseNotification
     */
    private $notification;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param DatabaseNotification $notification
     * @param Request $request
     */
    public function __construct(DatabaseNotification $notification, Request $request)
    {
        $this->middleware('auth');
        $this->notification = $notification;
        $this->request = $request;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $pagination = Auth::user()->notifications()->paginate($this->request->get('perPage', 10));

        $pagination->transform(function(DatabaseNotification $notification) {
            $notification->relative_created_at = $notification->created_at->diffForHumans();
            if ($notification->created_at->isCurrentDay()) {
                $notification->time_period = 'today';
            } else if ($notification->created_at->isYesterday()) {
                $notification->time_period = 'yesterday';
            } else if ($notification->created_at->isBetween(Carbon::now()->subDay(), Carbon::now()->subDays(8))) {
                $notification->time_period = 'last 7 days';
            } else {
                $notification->time_period = 'older';
            }
            return $notification;
        });

        return $this->success(['pagination' => $pagination]);
    }

    public function markAsRead()
    {
        $this->validate($this->request, [
            'ids' => 'required|array',
        ]);

        $now = Carbon::now();

        $this->notification
            ->whereIn('id', $this->request->get('ids'))
            ->update(['read_at' => $now]);

        $unreadCount = Auth::user()->unreadNotifications()->count();

        return $this->success(['unreadCount' => $unreadCount, 'date' => $now]);
    }

    public function destroy($ids)
    {
        $ids = explode(',', $ids);
        Auth::user()->notifications()->whereIn('id', $ids)->delete();
    }
}
