<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function globalEmail()
    {
        $pageTitle = 'Global Email Template';
        return view('admin.notification.global_email_template', compact('pageTitle'));
    }

    public function globalEmailUpdate(Request $request)
    {
        $request->validate([
            'email_from' => 'required|email|string|max:40',
            'email_from_name' => 'required',
            'email_template' => 'required',
        ]);

        $general = gs();
        $general->email_from = $request->email_from;
        $general->email_from_name = $request->email_from_name;
        $general->email_template = $request->email_template;
        $general->save();

        $notify[] = ['success', 'Global email template updated successfully'];
        return back()->withNotify($notify);
    }
    public function templates()
    {
        $pageTitle = 'Notification Templates';
        $templates = NotificationTemplate::orderBy('name')->get();
        return view('admin.notification.template.index', compact('pageTitle', 'templates'));
    }

    public function templateEdit($type, $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $pageTitle = $template->name;
        
        $emailTemplate = str_replace('{{message}}', 'timeMessageBodyTemp', gs('email_template'));

        return view('admin.notification.template.' . $type, compact('pageTitle', 'template', 'emailTemplate'));
    }

    public function templateUpdate(Request $request, $type, $id)
    {
        $validationRule = [];
        if ($type == 'email') {
            $validationRule = [
                'subject' => 'required|string|max:255',
                'email_body' => 'required',
            ];
        }
        $request->validate($validationRule);
        $template = NotificationTemplate::findOrFail($id);
        if ($type == 'email') {
            $template->subject = $request->subject;
            $template->email_body = $request->email_body;
            $template->email_sent_from_name = $request->email_sent_from_name;
            $template->email_sent_from_address = $request->email_sent_from_address;
            $template->email_status = $request->email_status ? Status::ENABLE : Status::DISABLE;
            $template->email_heading = $request->email_heading;
        }
        $template->save();

        $notify[] = ['success', 'Notification template updated successfully'];
        return back()->withNotify($notify);
    }

    public function emailSetting()
    {
        $pageTitle = 'Email Notification Settings';
        return view('admin.notification.email_setting', compact('pageTitle'));
    }

    public function emailSettingUpdate(Request $request)
    {
        $request->validate([
            'email_method' => 'required|in:php,smtp,sendgrid,mailjet',
            'host' => 'required_if:email_method,smtp',
            'port' => 'required_if:email_method,smtp',
            'username' => 'required_if:email_method,smtp',
            'password' => 'required_if:email_method,smtp',
            'enc' => 'required_if:email_method,smtp',
            'appkey' => 'required_if:email_method,sendgrid',
            'public_key' => 'required_if:email_method,mailjet',
            'secret_key' => 'required_if:email_method,mailjet',
        ], [
            'host.required_if' => 'The :attribute is required for SMTP configuration',
            'port.required_if' => 'The :attribute is required for SMTP configuration',
            'username.required_if' => 'The :attribute is required for SMTP configuration',
            'password.required_if' => 'The :attribute is required for SMTP configuration',
            'enc.required_if' => 'The :attribute is required for SMTP configuration',
            'appkey.required_if' => 'The :attribute is required for SendGrid configuration',
            'public_key.required_if' => 'The :attribute is required for Mailjet configuration',
            'secret_key.required_if' => 'The :attribute is required for Mailjet configuration',
        ]);
        if ($request->email_method == 'php') {
            $data['name'] = 'php';
        } else if ($request->email_method == 'smtp') {
            $request->merge(['name' => 'smtp']);
            $data = $request->only('name', 'host', 'port', 'enc', 'username', 'password', 'driver');
        } else if ($request->email_method == 'sendgrid') {
            $request->merge(['name' => 'sendgrid']);
            $data = $request->only('name', 'appkey');
        } else if ($request->email_method == 'mailjet') {
            $request->merge(['name' => 'mailjet']);
            $data = $request->only('name', 'public_key', 'secret_key');
        }
        $general = gs();
        $general->mail_config = $data;
        $general->save();
        $notify[] = ['success', 'Email settings updated successfully'];
        return back()->withNotify($notify);
    }

    public function emailTest(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $config = gs('mail_config');
        $receiverName = explode('@', $request->email)[0];
        $subject = strtoupper($config->name) . ' Configuration Success';
        $message = 'Your email notification setting is configured successfully for ' . gs('site_name');

        if (gs('en')) {
            $user = [
                'username' => $request->email,
                'email' => $request->email,
                'fullname' => $receiverName,
            ];
            notify($user, 'DEFAULT', [
                'subject' => $subject,
                'message' => $message,
            ], ['email'], false);
        } else {
            $notify[] = ['info', 'Please enable from general settings'];
            $notify[] = ['error', 'Your email notification is disabled'];
            return back()->withNotify($notify);
        }

        if (session('mail_error')) {
            $notify[] = ['error', session('mail_error')];
        } else {
            $notify[] = ['success', 'Email sent to ' . $request->email . ' successfully'];
        }

        return back()->withNotify($notify);
    }
}
