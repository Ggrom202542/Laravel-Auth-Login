<?php

namespace App\Mail;

use App\Models\User;
use App\Models\RegistrationApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $approval;
    public $loginUrl;

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
        $this->loginUrl = url('/login');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('ยินดีด้วย! การสมัครสมาชิกของคุณได้รับการอนุมัติแล้ว')
                    ->view('emails.registration-approved')
                    ->with([
                        'user' => $this->user,
                        'approval' => $this->approval,
                        'loginUrl' => $this->loginUrl,
                        'approvedDate' => $this->approval->approved_at->format('d/m/Y H:i:s')
                    ]);
    }
}
