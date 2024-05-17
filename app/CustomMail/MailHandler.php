<?php
namespace App\CustomMail;
use App\Jobs\SendEmailJob;
use App\Models\Mailtemplate;
use Mail;
use Config;

class MailHandler
{
    public static function mailsend($template_slug,$body)
    {
        // $bodyParams['cc']=['hiteshrudani123@gmail.com'];
        // $bodyParams['bcc']=['hiteshrudani123@gmail.com'];
        // $bodyParams['attachments']=['https://sitechecker.pro/wp-content/uploads/2017/12/URL-meaning.png'];

        if(!$template_slug){
            return true;
        }
        $mailtemplate=Mailtemplate::select('name','subject','message','from_name','is_plain_text')->where('is_active',1)->where('slug',$template_slug)->first();
        if(!$mailtemplate){
            return true;
        }
        $metaData=companymeta();
        setSMTPConfig();
        $subject = $mailtemplate->subject;
        $message_txt = $mailtemplate->message;
        if($mailtemplate->is_plain_text==0){
            $replacedata=isset($body['replacedata_array'])?$body['replacedata_array']:[];
            $replacedata['{email_signature}']=$metaData['email_signature'];
            $message_txt = str_replace(array_keys($replacedata),array_values($replacedata),$message_txt);
            $subject = str_replace(array_keys($replacedata),array_values($replacedata),$subject);
        }
        $body['data']['body_data']=$message_txt;
        $body['data']['email_template_header']=$metaData['email_template_header'];
        $body['data']['email_template_footer']=$metaData['email_template_footer'];
        $email = isset($body['to'])?$body['to']:'';
        $template="emails.common";

        try {
            if (env('QUEUE_JOB_ENABLE')) {
                dispatch(new SendEmailJob($body, $email, $subject, $template));
            } else {
                self::mailPass($body, $email, $subject, $template);
            }
        } catch (Exception $exception) {
            throw $exception;
        }
    }


    public static function mailPass($body, $email, $subject, $template)
    {
        try{
            if($body && $email && $subject && $template){
                $bodydata=isset($body['data'])?$body['data']:[];
                Mail::send($template,$bodydata, function($message) use ($email,$subject,$body)
                {
                    $message->from(config('mail.mailers.smtp.username'),config('mail.mailers.smtp.fromname'));
                    $message->subject($subject);
                    $message->to($email);
        
                    $attachments = isset($body['attachments'])?$body['attachments']:'';
                    if (!empty($attachments)) {
                        if (!is_array($attachments)) {
                            $attachments = [$attachments];
                        }
                        foreach ($attachments as $file) {
                            $message->attach($file);
                        }
                    }
        
                    if (isset($body['cc'])) {
                        $message->cc($body['cc']);
                    }
        
                    if (isset($body['bcc'])) {
                        $message->bcc($body['bcc']);
                    }
                });  
            }
        } catch(Exception $exception){
            throw $exception;
        }
    }
}