<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

Route::get('emailassets/images/{trx}', 'CronController@emailTrack')->name('email.track');

Route::get('cron', 'CronController@cron')->name('cron');
Route::get('auth', 'LinkLoginController@handle');

Route::get('web-login', function(){
    return to_route('user.login');
})->name('login');



Route::controller('CronController')->prefix('cron')->name('cron.')->group(function () {
    Route::get('generate-invoice', 'generateInvoice')->name('invoice.generate');
    Route::get('apply-late-fee', 'applyLateFee')->name('late.fee.apply');
    Route::get('suspend-organization', 'suspendOrganization')->name('organization.suspend');
    Route::get('send-email', 'sendEmail')->name('email.send');
    Route::get('summary-mail-queue', 'summaryMailQueue')->name('summary.mail.queue');
    Route::get('daily-summary-mail', 'dailySummaryMail')->name('daily.summary.mail');
    Route::get('upload-failed-screenshot', 'uploadFailedScreenshot')->name('screenshot.failed.upload');
    Route::get('engagement-emails', 'engagementEmails')->name('engagement.emails');
});

Route::namespace('User\Auth')->name('user.')->middleware('guest')->group(function () {
    Route::controller('LoginController')->group(function () {
        Route::get('/', 'showLoginForm')->name('login');
        Route::post('/login', 'login')->name('login.submit');
        Route::get('logout', 'logout')->middleware('auth')->withoutMiddleware('guest')->name('logout');
    });

    Route::controller('RegisterController')->group(function () {
        Route::get('join/{referralCode}', 'referralJoin')->name('join.referral');
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register');
        Route::post('check-user', 'checkUser')->name('checkUser')->withoutMiddleware('guest');
    });

    Route::controller('MemberRegisterController')->group(function () {
        Route::get('invitation/join/{invitationCode}', 'showGeneralJoinForm')->name('invitation.join');
        Route::get('invitaiton/member/join/{invitationCode}', 'showEmailJoinForm')->name('invitation.member.join');
        Route::post('join-member', 'register')->name('join.register');
        Route::get('team/join/confirm', 'joinConfirm')->name('join.team.confirm');
    });

    Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
        Route::get('reset', 'showLinkRequestForm')->name('request');
        Route::post('email', 'sendResetCodeEmail')->name('email');
        Route::get('code-verify', 'codeVerify')->name('code.verify');
        Route::post('verify-code', 'verifyCode')->name('verify.code');
    });

    Route::controller('ResetPasswordController')->group(function () {
        Route::post('password/reset', 'reset')->name('password.update');
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
    });

    Route::controller('SocialiteController')->group(function () {
        Route::get('social-login/{provider}', 'socialLogin')->name('social.login');
        Route::get('social-login/callback/{provider}', 'callback')->name('social.login.callback');
    });
});

Route::get('verify-email-link/{id}/{code}', 'User\AuthorizationController@verifyEmailLink')
    ->name('user.verify.email.link')
    ->middleware('signed');
Route::get('email-verified', 'User\AuthorizationController@emailVerified')
    ->name('user.email.verified');

Route::middleware('auth')->name('user.')->group(function () {

    // suspended route
    Route::get('suspended', 'User\AuthorizationController@suspended')->name('suspended');

    Route::controller('User\OrganizationController')->group(function () {
        Route::get('organization/info', 'userOrganization')->name('organization');
        Route::post('organization-submit', 'userOrganizationSubmit')->name('organization.submit');
        Route::post('coupon/check', 'checkCoupon')->name('coupon.check');
    });

    //authorization
    Route::middleware('registration.complete')->namespace('User')->controller('AuthorizationController')->group(function () {
        Route::get('send-email-verify', 'sendEmailVerLink')->name('send.email.ver.link');
        Route::get('authorization', 'authorizeForm')->name('authorization');
    });

    Route::middleware(['check.status', 'registration.complete'])->group(function () {

        Route::namespace('User')->group(function () {

            Route::controller('UserController')->group(function () {
                Route::get('dashboard', 'home')->name('home');
                Route::get('onboarding/skip', 'skipOnboarding')->name('onboarding.skip');
                Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');
                Route::get('project-timings', 'projectTimings')->name('project.timings');
                Route::get('app-uses', 'appUses')->name('app.uses');
                Route::get('time-tracking', 'timeTrackingData')->name('time.tracking');
                Route::get('activity-series', 'activitySeriesData')->name('activity.series')->middleware('role:manager,organizer');

                Route::get('summary-statistics', 'summaryStatistics')->name('summary.statistics');
                Route::get('top-activity-staff', 'topActivityStaff')->name('top.activity.staff')->middleware('role:organizer,manager');

                //Report
                Route::middleware('role:organizer')->group(function () {
                    Route::any('deposits', 'depositHistory')->name('deposit.history');
                    // Route::get('billing-overview', 'billingOverview')->name('billing.overview');
                    Route::get('transactions', 'transactions')->name('transactions');
                });

                Route::middleware('role:organizer,manager')->group(function () {
                    Route::get('top-performers', 'topPerformers')->name('top.performers');
                    Route::get('low-performers', 'lowPerformers')->name('low.performers');
                });

                //Notification
                Route::get('notifications', 'notifications')->name('notifications');
                Route::get('notification/read/{id}', 'notificationRead')->name('notification.read');
                Route::get('notifications/read-all', 'readAllNotification')->name('notifications.read.all');
                Route::post('notifications/delete-all', 'deleteAllNotification')->name('notifications.delete.all');
                Route::post('notifications/delete-single/{id}', 'deleteSingleNotification')->name('notifications.delete.single');
            });

            //Profile setting
            Route::controller('AccountSettingController')->prefix('/account-setting')->name('account.setting.')->group(function () {
                Route::get('profile-setting', 'profile')->name('profile');
                Route::post('profile-setting', 'submitProfile');
                Route::post('upload/image', 'uploadImage')->name('upload.image');
                Route::get('change-password', 'changePassword')->name('change.password');
                Route::post('change-password', 'submitPassword');

                Route::middleware('role:organizer')->group(function () {
                    Route::get('organization', 'organizationSetting')->name('organization');
                    Route::post('organization/upload/logo', 'uploadLogo')->name('organization.upload.logo');
                    Route::post('organization/update', 'organizationUpdate')->name('organization.update');

                    // Referral page
                    Route::get('referral', 'referral')->name('referral');
                    Route::post('referral/update', 'referralUpdate')->name('referral.update');
                });
            });

            Route::controller('ActivityController')->name('activity.')->prefix('activity')->group(function () {
                Route::get('screenshots/load', 'loadScreenshots')->name('screenshot.load');
                Route::get('screenshots/slice', 'loadSliceScreenshots')->name('screenshot.slice.load');
                Route::get('screenshots/{uid?}', 'screenshots')->name('screenshot.index');

            });

            Route::controller('ProjectController')->prefix('project')->name('project.')->group(function () {
                Route::get('/', 'list')->name('list');
                Route::get('/details/{uid}', 'details')->name('details');

                Route::middleware('role:manager,organizer')->group(function () {
                    Route::post('/save/{uid?}', 'save')->name('save');
                    Route::post('/tasks/{projectId}/{id?}', 'saveTask')->name('task.save');
                    Route::post('/{projectId}/members/{uid}/remove', 'removeUser')->name('member.remove');
                    Route::post('assign/member/{projectId}', 'assignMember')->name('assign.member');
                });
            });

            // member route
            Route::controller('MemberController')->prefix('member')->middleware('role:manager,organizer')->name('member.')->group(function () {
                Route::get('list', 'memberList')->name('list');
                Route::get('pending', 'pendingMember')->name('pending');

                Route::post('generate/invitation/link', 'generateInvitationLink')->name('generate.invitation.link');
                Route::post('invitation/delete/{id}', 'deleteInvitation')->name('invitation.delete');
                Route::post('registration', 'memberRegistration')->name('registration')->middleware('email.verified');
                Route::post('invitation/send', 'sendInvitation')->name('invitation.send')->middleware('email.verified');
                Route::post('tracking/status/{memberId}', 'changeTrackingStatus')->name('tracking.status');
                Route::post('status/{memberId}', 'changeStatus')->name('status');
                Route::post('status/approve/{memberId}', 'approve')->name('status.approve');
                Route::post('status/reject/{memberId}', 'reject')->name('status.reject');
                Route::get('details/{memberId}', 'details')->name('details');
                
                Route::post('phone/update/{uid}', 'updatePhone')->name('phone.update');
                Route::post('role/update/{uid}', 'updateRole')->name('role.update');
                Route::post('project/add/{uid}', 'addProject')->name('project.add');
                Route::post('project/remove/{uid}/{project_uid}', 'removeProject')->name('project.remove');

                Route::post('check', 'checkUser')->name('checkUser');
            });

            Route::controller('TimeSheetController')->prefix('time/')->name('time.')->group(function () {
                Route::get('calender/{uid?}', 'timeCalender')->name('calender');
                Route::get('weekly-worklog/{uid?}', 'timeWeekly')->name('weekly.worklog');
                Route::get('weekly-data/load', 'loadWeekly')->name('weekly.load');
                Route::get('load-calender', 'loadCalender')->name('load.calender');
            });

            // storage setting routes
            Route::middleware('role:organizer')->group(function () {
                Route::controller('StorageController')->prefix('setting/storage')->name('setting.storage.')->group(function () {
                    Route::get('/', 'list')->name('list');
                    Route::post('activate/{id}', 'activate')->name('activate');
                    Route::post('deactivate/{id}', 'deactivate')->name('deactivate');
                    Route::post('store/{id?}', 'store')->name('store');
                    Route::get('verify/{id}', 'verify')->name('verify');
                });

                //InvoiceController
                Route::controller('InvoiceController')->prefix('invoice')->name('invoice.')->group(function () {
                    Route::get('list', 'invoiceList')->name('list');
                    Route::post('pay', 'invoicePay')->name('pay');

                    Route::get('download/{invoice_number}', 'invoiceDownload')->name('download');
                });
            });

            Route::controller('TimeAndActivityController')->prefix('report/time-activity')->name('report.time.activity.')->group(function () {
                Route::get('/', 'timeAndActivity')->name('index');
                Route::get('load', 'loadTimeAndActivity')->name('load');
            });

            Route::controller('ReportController')->prefix('report')->name('report.')->group(function () {
                Route::get('time-analytics', 'timeAnalytics')->name('time.analytics');
                Route::get('time-analytics/load', 'loadTimeAnalytics')->name('time.analytics.load');

                Route::get('app-analytics', 'appAnalytics')->name('app.analytics');
                Route::get('app-analytics/load', 'loadAppAnalytics')->name('app.analytics.load');

                Route::get('project-timing', 'projectTiming')->name('project.timing');
                Route::get('project-timing/load', 'loadProjectTiming')->name('project.timing.load');

                Route::get('time-sheet', 'monthlyTimeSheet')->name('monthly.time.sheet')->middleware('role:manager,organizer');
                Route::get('time-sheet/load', 'loadMonthlyTimeSheet')->name('monthly.time.sheet.load')->middleware('role:manager,organizer');;
            });

            Route::controller('AppUsageReportController')->prefix('report')->name('report.')->group(function () {
                Route::get('app-usage', 'appUsage')->name('app.usage');
                Route::get('app-usage/load', 'loadAppUsage')->name('app.usage.load');
            });

            Route::controller('PerformerController')->middleware('role:organizer,manager')->prefix('performance')->name('performer.')->group(function () {

                Route::get('top', 'top')->name('top');
                Route::get('top/load', 'loadTop')->name('top.load');

                Route::get('low', 'low')->name('low');
                Route::get('low/load', 'loadLow')->name('low.load');

                Route::get('leaderboard', 'leaderboard')->name('leaderboard');
                Route::get('leaderboard/load', 'loadLeaderboard')->name('leaderboard.load');

                Route::get('leaderboard/mail/send/{month}/{year}', 'leaderboardMailSend')->name('leaderboard.mail.send');
                Route::get('leaderboard/download/{month}/{year}', 'leaderboardDownload')->name('leaderboard.download');
            });
        });

        // Payment
        Route::prefix('deposit')->name('deposit.')->controller('Gateway\PaymentController')->group(function () {
            Route::post('insert', 'depositInsert')->name('insert');
            Route::post('quick', 'quickDeposit')->name('quick');
            Route::get('confirm', 'depositConfirm')->name('confirm');
        });
    });
});


// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::middleware('role:organizer,manager')->group(function() {
        Route::get('/', 'supportTicket')->name('index');
        Route::get('new', 'openSupportTicket')->name('open');
        Route::post('create', 'storeSupportTicket')->name('store');
    });
    Route::post('reply/{id}', 'replyTicket')->name('reply');
    Route::post('close/{id}', 'closeTicket')->name('close');
    Route::get('details/{ticket}', 'viewTicket')->name('view');
    Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
});
