<?php

namespace Common\Settings\Mail;

use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;

class GmailApiMailTransport extends Transport
{

    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $response = app(GmailClient::class)->sendEmail($message->toString());

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }
}
