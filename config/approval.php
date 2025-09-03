<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Registration Approval Settings
    |--------------------------------------------------------------------------
    |
    | This file contains all the configurable options for the registration
    | approval system. You can control various aspects of how approvals
    | work in your application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Super Admin Permissions
    |--------------------------------------------------------------------------
    |
    | Control what super admin can do with the approval system
    |
    */
    'super_admin' => [
        'can_approve_registrations' => env('SUPER_ADMIN_CAN_APPROVE', true),
        'can_override_admin_decisions' => env('SUPER_ADMIN_CAN_OVERRIDE', true),
        'can_bulk_actions' => env('SUPER_ADMIN_CAN_BULK_ACTION', true),
        'requires_override_reason' => env('SUPER_ADMIN_OVERRIDE_REASON_REQUIRED', true),
        'can_delete_approvals' => env('SUPER_ADMIN_CAN_DELETE_APPROVALS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Permissions
    |--------------------------------------------------------------------------
    |
    | Control what admin can do with the approval system
    |
    */
    'admin' => [
        'can_approve_registrations' => env('ADMIN_CAN_APPROVE', true),
        'can_bulk_actions' => env('ADMIN_CAN_BULK_ACTION', true),
        'can_see_all_approvals' => env('ADMIN_CAN_SEE_ALL', false), // false = เห็นเฉพาะที่ตนเองยังไม่ได้จัดการ
        'auto_assign_to_admin' => env('ADMIN_AUTO_ASSIGN', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflow Settings
    |--------------------------------------------------------------------------
    |
    | Configure how the approval workflow behaves
    |
    */
    'workflow' => [
        'require_admin_first' => env('APPROVAL_REQUIRE_ADMIN_FIRST', false), // Super Admin ต้องให้ Admin ดูก่อน
        'escalation_days' => env('APPROVAL_ESCALATION_DAYS', 3), // อัตโนมัติแจ้ง Super Admin หลังจาก X วัน
        'auto_reject_days' => env('APPROVAL_AUTO_REJECT_DAYS', 30), // Auto reject หลังจาก X วัน
        'email_notifications' => env('APPROVAL_EMAIL_NOTIFICATIONS', true),
        'detailed_audit_log' => env('APPROVAL_DETAILED_AUDIT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure who gets notified when
    |
    */
    'notifications' => [
        'notify_super_admin_on_new_registration' => env('NOTIFY_SUPER_ADMIN_NEW_REG', false),
        'notify_super_admin_on_pending_escalation' => env('NOTIFY_SUPER_ADMIN_ESCALATION', true),
        'notify_admin_on_super_admin_override' => env('NOTIFY_ADMIN_OVERRIDE', true),
        'daily_summary_enabled' => env('APPROVAL_DAILY_SUMMARY', true),
        'weekly_report_enabled' => env('APPROVAL_WEEKLY_REPORT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI & Display Settings
    |--------------------------------------------------------------------------
    |
    | Control how the approval interface appears
    |
    */
    'ui' => [
        'items_per_page' => env('APPROVAL_ITEMS_PER_PAGE', 20),
        'show_statistics' => env('APPROVAL_SHOW_STATISTICS', true),
        'highlight_escalated' => env('APPROVAL_HIGHLIGHT_ESCALATED', true),
        'show_approval_timeline' => env('APPROVAL_SHOW_TIMELINE', true),
        'compact_view_default' => env('APPROVAL_COMPACT_VIEW', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security & Audit Settings
    |--------------------------------------------------------------------------
    |
    | Configure security and auditing features
    |
    */
    'security' => [
        'log_all_approval_actions' => env('APPROVAL_LOG_ALL_ACTIONS', true),
        'require_reason_for_rejection' => env('APPROVAL_REQUIRE_REJECTION_REASON', true),
        'require_reason_for_override' => env('APPROVAL_REQUIRE_OVERRIDE_REASON', true),
        'ip_logging_enabled' => env('APPROVAL_LOG_IP', true),
        'user_agent_logging' => env('APPROVAL_LOG_USER_AGENT', false),
    ],

];
