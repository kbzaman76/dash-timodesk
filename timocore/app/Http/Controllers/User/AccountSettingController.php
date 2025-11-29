<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountSettingController extends Controller {
    public function profile() {
        $pageTitle    = "Profile Setting";
        $user         = auth()->user();
        $organization = auth()->user()->organization;

        return view('Template::user.account_setting.profile', compact('pageTitle', 'user', 'organization'));
    }

    public function submitProfile(Request $request) {
        $user = auth()->user();

        $request->validate([
            'fullname' => 'required|string|max:40',
        ], [
            'fullname.required' => 'The first name field is required',
            'fullname.string'   => 'The last name field is string',
        ]);

        $user->fullname = $request->fullname;

        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return back()->withNotify($notify);
    }


    public function uploadImage(Request $request) {
        $user = auth()->user();

        $request->validate([
            'image'    => ['required', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

            try {
                $webpFile = toWebpFile($request->file('image'), getFileSize('userProfile'));

                $location                 = $user->organization->uid . '/user';
                [$oldStorageId, $oldPath] = getImageInfo($user->image);

                [$fileName, $storageId] = uploadPermanentImage($webpFile, $location);

                if ($fileName || $storageId) {

                    $image       = $storageId . '|' . $fileName;
                    $user->image = $image;
                    $user->save();

                    if ($oldPath && $oldStorageId) {
                        deleteStorageFile($oldPath, $oldStorageId);
                    }
                }
            } catch (\Exception $exp) {
                $message = 'Logo saved fail';
                return response()->json($message, 422);
            }


        $user->save();
        $message = 'Image saved successfully';
        return response()->json($message, 200);
    }


    public function changePassword() {
        $pageTitle    = 'Change Password';
        $organization = auth()->user()->organization;

        return view('Template::user.account_setting.password', compact('pageTitle', 'organization'));
    }

    public function submitPassword(Request $request) {

        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', $passwordValidation],
        ]);

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $password       = Hash::make($request->password);
            $user->password = $password;
            $user->save();
            $notify[] = ['success', 'Password changed successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'The password doesn\'t match!'];
            return back()->withNotify($notify);
        }
    }

    public function organizationSetting() {
        $pageTitle    = 'Organization Setting';
        $organization = auth()->user()->organization;
        $countries    = json_decode(file_get_contents(resource_path('views/partials/country.json')), true);
        $timezones    = \DateTimeZone::listIdentifiers();

        return view('Template::user.account_setting.organization', compact('pageTitle', 'organization', 'countries', 'timezones'));
    }


    public function uploadLogo(Request $request) {
        $organization = myOrganization();

        $request->validate([
            'logo'    => ['required', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

            try {
                $webpFile = toWebpFile($request->file('logo'), getFileSize('organization'));
                $location = $organization->uid . '/logo';

                [$fileName, $storageId] = uploadPermanentImage($webpFile, $location);

                if ($fileName || $storageId) {

                    [$oldStorageId, $oldPath] = getImageInfo($organization->logo);

                    $organization->logo = $storageId . '|' . $fileName;

                    if ($oldPath && $oldStorageId) {
                        deleteStorageFile($oldPath, $oldStorageId);
                    }
                }

            } catch (\Exception $exp) {
                $message = 'Logo saved fail';
                return response()->json($message, 422);
            }


        $organization->save();
        $message = 'Logo saved successfully';
        return response()->json($message, 200);
    }

    public function organizationUpdate(Request $request) {
        $organization = myOrganization();

        $request->validate([
            'organization_name' => 'required|string|max:255',
            'address'           => 'nullable|string|max:255',
            'timezone'          => 'max:255|required|in:' . (implode(',', \DateTimeZone::listIdentifiers())),
        ]);
        $organization->timezone = $request->timezone;
        $organization->name     = $request->organization_name;
        $organization->save();

        $user          = $organization->user;
        $user->address = $request->address;
        $user->save();

        $notify[] = ['success', 'Organization updated successfully'];
        return back()->withNotify($notify);
    }

    public function referral() {
        $pageTitle    = 'Referral Option';

        $organization = myOrganization();
        $referrals = Organization::withCount(['deposits' => function($query) {
            $query->successful();
        }])->where('referred_by', $organization->id)->orderBy('id', 'desc')->paginate(getPaginate());

        return view('Template::user.account_setting.referral', compact('pageTitle', 'organization', 'referrals'));
    }

    public function referralUpdate(Request $request) {
        $organization = auth()->user()->organization;

        $request->validate([
            'referral_code' => [
                'required',
                'alpha_dash',
                'min:3',
                'max:32',
                Rule::unique('organizations', 'referral_code')->ignore($organization->id),
            ],
        ]);

        $organization->referral_code = strtolower($request->referral_code);
        $organization->save();

        $notify[] = ['success', 'Referral code updated'];
        return back()->withNotify($notify);
    }
}
