<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowBalanceAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $currentBalance;
    public $threshold;

    public function __construct($currentBalance, $threshold)
    {
        $this->currentBalance = $currentBalance;
        $this->threshold = $threshold;
    }

    public function build()
    {
        return $this->subject('⚠️ Low Onfon Balance Alert')
                    ->view('emails.low-balance-alert')
                    ->with([
                        'currentBalance' => $this->currentBalance,
                        'threshold' => $this->threshold,
                        'alertTime' => now()->format('Y-m-d H:i:s'),
                    ]);
    }
}