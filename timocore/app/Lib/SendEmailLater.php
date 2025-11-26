<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use PHPMailer\PHPMailer\PHPMailer;

class SendEmailLater
{
    public function notifyWithQueue($user, $templateName, $shortCodes = null, $sendVia = null, $createLog = true, $pushImage = null)
    {
        $globalShortCodes = [
            'site_name'       => gs('site_name'),
            'site_currency'   => gs('cur_text'),
            'currency_symbol' => gs('cur_sym'),
        ];

        if (gettype($user) == 'array') {
            $user = (object) $user;
        }

        $shortCodes = array_merge($shortCodes ?? [], $globalShortCodes);

        $notificationTemplate = NotificationTemplate::where('act', $templateName)->first();

        if ($notificationTemplate) {
            $general = gs();

            $body           = 'email_body';
            $globalTemplate = $general->email_template;

            $message = $this->replaceShortCode(toTitle($user->fullname), toTitle($user->fullname), $globalTemplate, $notificationTemplate->$body);

            if (empty($message)) {
                $message = $notificationTemplate->$body;
            }

            if ($shortCodes) {
                $message = $this->replaceTemplateShortCode($message, $shortCodes);
            }

            if ($notificationTemplate->email_heading) {
                $message = str_replace("{{email_heading}}", $notificationTemplate->email_heading, $message);
            }else{
                $message = str_replace("{{email_heading}}", '', $message);
            }

            $randomTrx = strtolower(getTrx(12));
            $message = str_replace("{{track_image}}", route('email.track', $randomTrx . '.png'), $message);


            $subject = $notificationTemplate->subject;
            $subject = $this->replaceTemplateShortCode($subject,$shortCodes);
            
            $notificationLog                    = new NotificationLog();
            $notificationLog->user_id           = $user->id;
            $notificationLog->notification_type = 'email';
            $notificationLog->sender            = $general->mail_config->name ?? 'php';
            $notificationLog->sent_from_name    = $this->replaceTemplateShortCode($this->template->email_sent_from_name ?? $general->site_name);
            $notificationLog->sent_from         = $notificationTemplate->email_sent_from_address ?? $general->email_from;
            $notificationLog->sent_to           = $user->email;
            $notificationLog->sent_to_name      = $user->fullname;
            $notificationLog->subject           = $subject;
            $notificationLog->image             = null;
            $notificationLog->message           = $message;
            $notificationLog->is_send           = Status::NO;
            $notificationLog->trx_key           = $randomTrx;
            $notificationLog->save();
        }

    }

    public function sendPhpMail($log)
    {
        $sentFromName  = $log->sent_from_name;
        $sentFromEmail = $log->sent_from;
        $headers       = "From: $sentFromName <$sentFromEmail> \r\n";
        $headers .= "Reply-To: $sentFromName <$sentFromEmail> \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        @mail($log->sent_to, $log->subject, $log->message, $headers);
    }

    public function sendSmtpMail($log)
    {
        $mail   = new PHPMailer(true);
        $config = gs('mail_config');
        //Server settings
        $mail->isSMTP();
        $mail->Host     = $config->host;
        $mail->SMTPAuth = true;
        $mail->Username = $config->username;
        $mail->Password = $config->password;
        if ($config->enc == 'ssl') {
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->Port    = $config->port;
        $mail->CharSet = 'UTF-8';
        //Recipients
        $mail->setFrom($log->sent_from, $log->sent_from_name);
        $mail->addAddress($log->sent_to, $log->sent_to_name);
        $mail->addReplyTo($log->sent_from, $log->sent_from_name);
        // Content
        $mail->isHTML(true);
        $mail->Subject = $log->subject;
        $mail->Body    = $log->message;
        $mail->send();
    }

    public function replaceShortCode($name, $username, $template, $body)
    {
        if (is_array($username)) {
            $username = implode(',', $username);
        }
        $message = str_replace("{{fullname}}", $name, $template);
        $message = str_replace("{{username}}", $username, $message);
        $message = str_replace("{{message}}", $body, $message);
        return $message;
    }

    public function replaceTemplateShortCode($content, $shortCodes = [])
    {
        foreach ($shortCodes ?? [] as $code => $value) {
            $content = str_replace('{{' . $code . '}}', $value, $content);
        }
        return $content;
    }

}
