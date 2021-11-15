<?php namespace Common\Pages;

use App\User;
use Common\Core\BaseController;
use Common\Notifications\ContactPageMessage;
use Common\Settings\Settings;
use Illuminate\Http\Request;

class ContactPageController extends BaseController
{
    public function sendMessage(Request $request)
    {
        if ( ! config('common.site.enable_contact_page')) return abort(404);

        $this->validate($request, [
            'name' => 'required|string|min:5',
            'email' => 'required|email',
            'message' => 'required|string|min:10'
        ]);

        $notification = new ContactPageMessage($request->all());

        (new User())->forceFill([
            'name' => config('mail.from.name'),
            'email' => app(Settings::class)->get('mail.contact_page_address', config('mail.from.address')),
        ])->notify($notification);
    }
}
