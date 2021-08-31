<?php

namespace App\Http\Controllers;

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
        $pagination = Auth::user()->notifications()->paginate(5);

        $pagination->transform(function($notification) {
            $notification->relative_created_at = $notification->created_at->diffForHumans();
            return $notification;
        });

        return $this->success(['pagination' => $pagination]);
    }

    public function markAsRead()
    {
        $this->validate($this->request, [
            'ids' => 'required|array',
        ]);

        $this->notification
            ->whereIn('id', $this->request->get('ids'))
            ->update(['read_at' => Carbon::now()]);

        $unreadCount = Auth::user()->unreadNotifications()->count();

        return $this->success(['unreadCount' => $unreadCount]);
    }
}
