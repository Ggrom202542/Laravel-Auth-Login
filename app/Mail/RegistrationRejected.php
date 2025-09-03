<?php

namespace App\Mail;

use App\Models\User;
use App\Models\RegistrationApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationRejected extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $approval;
    public $rejectionReason;
    public $registerUrl;

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
        $this->rejectionReason = $approval->rejection_reason;
        $this->registerUrl = url('/register');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('แจ้งผลการพิจารณาการสมัครสมาชิก')
                    ->view('emails.registration-rejected')
                    ->with([
                        'user' => $this->user,
                        'approval' => $this->approval,
                        'rejectionReason' => $this->rejectionReason,
                        'registerUrl' => $this->registerUrl,
                        'rejectedDate' => $this->approval->rejected_at->format('d/m/Y H:i:s')
                    ]);
    }
}
