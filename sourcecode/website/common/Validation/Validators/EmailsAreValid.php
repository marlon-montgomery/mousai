<?php

namespace Common\Validation\Validators;

use App\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Concerns\ValidatesAttributes;

class EmailsAreValid implements Rule
{
    use ValidatesAttributes;

    /**
     * @var int
     */
    private $maxEmails = 5;

    /**
     * @var bool
     */
    private $validateExistence;

    /**
     * @var string
     */
    private $validationMessage;

    public function __construct($validateExistence = true)
    {
        $this->validateExistence = $validateExistence;
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $emails)
    {
        $invalidEmails = array_filter($emails, function($email) use($attribute) {
            return !$this->validateEmail($attribute, $email, []);
        });

        if ( ! empty($invalidEmails)) {
            $this->validationMessage = $this->invalidEmailsMessage($invalidEmails);
            return false;
        }

        if ($this->validateExistence) {
            $dbEmails = app(User::class)->whereIn('email', $emails)->pluck('email');
            $nonExistentEmails = array_filter($emails, function($email) use($dbEmails) {
                return !$dbEmails->contains($email);
            });
            if ( ! empty($nonExistentEmails)) {
                $this->validationMessage = $this->emailsDontExistMessage($nonExistentEmails);
                return false;
            }
        }

        return true;
    }

    private function invalidEmailsMessage(array $emails): string
    {
        $emailString = implode(', ', array_slice($emails, 0, $this->maxEmails));
        if (count($emails) > $this->maxEmails) {
            $emailString .= '...';
        }
        return trans("Invalid emails: :emails", ['emails' => $emailString]);
    }

    private function emailsDontExistMessage(array $emails): string
    {
        $emailString = implode(', ', array_slice($emails, 0, $this->maxEmails));
        if (count($emails) > $this->maxEmails) {
            $emailString .= '...';
        }
        return trans("Could not find users for emails: :emails", ['emails' => $emailString]);
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
       return $this->validationMessage;
    }
}
