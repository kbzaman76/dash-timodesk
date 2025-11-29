<?php

namespace App\Constants;

class Status{

    const ENABLE = 1;
    const DISABLE = 0;

    const YES = 1;
    const NO = 0;

    const VERIFIED = 1;
    const UNVERIFIED = 0;

    const PAYMENT_INITIATE = 0;
    const PAYMENT_SUCCESS = 1;
    const PAYMENT_PENDING = 2;
    const PAYMENT_REJECT = 3;

    CONST TICKET_OPEN = 0;
    CONST TICKET_ANSWER = 1;
    CONST TICKET_REPLY = 2;
    CONST TICKET_CLOSE = 3;

    CONST PRIORITY_LOW = 1;
    CONST PRIORITY_MEDIUM = 2;
    CONST PRIORITY_HIGH = 3;

    const USER_ACTIVE = 1;
    const USER_BAN = 0;
    const USER_PENDING = 2;
    const USER_REJECTED = 3;

    const CUR_BOTH = 1;
    const CUR_TEXT = 2;
    const CUR_SYM = 3;

    const FREE_TRIAL_DURATION = 15;
    const PLAN_DURATION = 30;

    const LATE_FEE_APPLY_HOURS = 72;
    const LATE_FEE_PERCENTAGE = 5;

    const SUSPEND_HOURS = 144;

    const INVOICE_UNPAID = 0;
    const INVOICE_PAID = 1;
    const INVOICE_CANCELLED = 9;


    const S3_STORAGE = 1;
    const FTP_STORAGE = 2;

    const ORGANIZER = 1;
    const MANAGER = 2;
    const STAFF = 3;

    const INACTIVE_STORAGE = 0;
    const ACTIVE_STORAGE = 1;
    const BACKUP_STORAGE = 2;
    const PERMANENT_STORAGE = 3;
}
