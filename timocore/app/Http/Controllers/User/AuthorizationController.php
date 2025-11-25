<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Constants\Status;
use App\Lib\Intended;
use App\Models\User;

class AuthorizationController extends Controller
{

    public function sendEmailVerLink()
    {
        $user = auth()->user();

        if ((int) $user->ev === Status::VERIFIED) {
            return back()->withNotify([['success', 'Email already verified']]);
        }

        $response = $user->sendEmailVerificationLink();

        if (!$response['status']) {
            throw ValidationException::withMessages([
                'resend' => 'Please try after '.$response['delay'].' seconds'
            ]);
        }

        return back()->withNotify([['success', 'Verification link sent to your email']]);
    }

    public function verifyEmailLink(Request $request, $id, $code)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }

        $user = User::findOrFail($id);

        $user->ev = Status::VERIFIED;
        $user->ver_code = null;
        $user->ver_code_send_at = null;
        if ($user->isFillable('email_verified_at')) {
            $user->email_verified_at = now();
        }
        $user->save();

        $redirection = Intended::getRedirection();
        $notify[] = ['success', 'Your email has been verified'];
        return $redirection ? $redirection : to_route('user.email.verified')->withNotify($notify);
    }

    public function authorizeForm()
    {
        $user = auth()->user();
        if (!$user->status) {
            $pageTitle = 'Banned';
            $type = 'ban';
        }else{
            return to_route('user.home');
        }

        return view('Template::user.auth.authorization.'.$type, compact('user', 'pageTitle'));

    }

    public function emailVerified()
    {
        $pageTitle = 'Email Verified';
        return view('Template::user.auth.authorization.email_verified', compact('pageTitle'));
    }

    public function suspended()
    {
        $organization = myOrganization();
        if ($organization?->is_suspend) {
            $pageTitle = 'Suspended';
            return view('Template::user.suspended', compact('pageTitle'));
        }else{
            return to_route('user.home');
        }
    }
}
