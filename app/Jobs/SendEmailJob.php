<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MailHandler;
use Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $body;
    public $emailto;
    public $subject;
    public $template;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($body,$email,$subject,$template)
    {
        $this->body=$body;
        $this->emailto=$email;
        $this->subject=$subject;
        $this->template=$template;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            setSMTPConfig();
            MailHandler::mailPass($this->body,$this->emailto,$this->subject,$this->template);
        }catch(Exception $e){
            Log::error("--- Send email job exception --- " .$e);
        }
    }
}
