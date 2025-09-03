<?php

namespace App\Mail;

use App\Models\User;
use App\Models\RegistrationApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationPending extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $approval;
    public $statusUrl;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param RegistrationApproval $approval
     */
    public function __construct(User $user, RegistrationApproval $approval)
    {
        $this->user = $user;
        $this->approval = $approval;
        $this->statusUrl = url('/approval-status/' . $approval->approval_token);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('ยืนยันการสมัครสมาชิก - รอการอนุมัติ')
                    ->view('emails.registration-pending')
                    ->with([
                        'user' => $this->user,
                        'approval' => $this->approval,
                        'statusUrl' => $this->statusUrl,
                        'submittedDate' => $this->approval->created_at->format('d/m/Y H:i:s')
                    ]);
    }
}
