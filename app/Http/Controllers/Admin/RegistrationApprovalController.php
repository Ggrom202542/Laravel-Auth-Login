<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationApproval;
use App\Models\User;
use App\Mail\RegistrationApproved;
use App\Mail\RegistrationRejected;
use App\Services\ApprovalAuditService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RegistrationApprovalController extends Controller
{
    protected ApprovalAuditService $auditService;

    public function __construct(ApprovalAuditService $auditService)
    {
        $this->middleware(['auth', 'role:admin,super_admin']);
        $this->auditService = $auditService;
    }

    /**
     * Display a listing of pending registration approvals.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';
        
        $query = RegistrationApproval::with(['user', 'reviewer', 'auditLogs.user'])
                                   ->orderBy('created_at', 'desc');

        // Role-based visibility
        if (!$isSuperAdmin && !config('approval.admin.can_see_all_approvals', false)) {
            // Admin เห็นเฉพาะที่ยังไม่มีคนดูแล หรือที่ตนเองเป็นคนดูแล
            $query->where(function ($q) use ($currentUser) {
                $q->where('reviewed_by', null) // ยังไม่มีคนดูแล
                  ->orWhere('reviewed_by', $currentUser->id); // หรือตนเองเป็นคนดูแล
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by user info
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Date filter
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Escalation filter (Super Admin เห็นรายการที่ค้างนานเป็นพิเศษ)
        if ($request->has('escalated') && $request->escalated === '1' && $isSuperAdmin) {
            $escalationDays = config('approval.workflow.escalation_days', 3);
            $query->where('status', 'pending')
                  ->where('created_at', '<=', now()->subDays($escalationDays));
        }

        $approvals = $query->paginate(config('approval.ui.items_per_page', 20));

        // Statistics - แตกต่างกันตาม Role
        $stats = $this->getStatistics($currentUser);

        // Recent override activities (สำหรับ Super Admin)
        $recentOverrides = $isSuperAdmin ? $this->auditService->getRecentOverrides(5) : collect();

        return view('admin.approvals.index', compact('approvals', 'stats', 'recentOverrides', 'isSuperAdmin'));
    }

    /**
     * Display the specified approval for detailed review.
     */
    public function show(RegistrationApproval $approval): View
    {
        $approval->load(['user', 'reviewer']);
        
        // Log view action
        $this->auditService->logView($approval);
        
        // Get audit trail for timeline display
        $auditTrail = $this->auditService->getApprovalTimeline($approval);
        
        // Check if current user can override decisions
        $canOverride = $this->auditService->canOverride();
        
        return view('admin.approvals.review', compact('approval', 'auditTrail', 'canOverride'));
    }

    /**
     * Approve a registration request.
     */
    public function approve(RegistrationApproval $approval, Request $request): RedirectResponse
    {
        if ($approval->status !== 'pending') {
            return redirect()->back()
                           ->with('error', 'การอนุมัติดังกล่าวได้ถูกดำเนินการแล้ว');
        }

        // Check for override scenario
        $isOverride = $approval->reviewed_by && $approval->reviewed_by !== Auth::id();
        $overrideReason = $request->input('override_reason');
        
        if ($isOverride) {
            if (!$this->auditService->canOverride()) {
                return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ Override การตัดสินใจ');
            }
            
            if ($this->auditService->overrideRequiresReason() && !$overrideReason) {
                return redirect()->back()->with('error', 'กรุณาระบุเหตุผลในการ Override');
            }
        }

        DB::beginTransaction();

        try {
            $oldStatus = $approval->status;
            $originalReviewer = $approval->reviewed_by;
            
            // Update approval record
            $approval->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Update user status
            $approval->user->update([
                'status' => 'active',
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]);

            // Log the approval action
            $this->auditService->logApproval(
                approval: $approval,
                reason: $overrideReason,
                isOverride: $isOverride,
                overriddenBy: $originalReviewer
            );

            DB::commit();

            // Refresh the model to get updated timestamps
            $approval->refresh();

            // Send approval email
            try {
                Mail::to($approval->user->email)->send(new RegistrationApproved($approval->user, $approval));
            } catch (\Exception $e) {
                // Log email error but don't fail the approval
                Log::error('Failed to send approval email: ' . $e->getMessage());
            }

            $message = $isOverride ? 
                'Override: อนุมัติการสมัครสมาชิกเรียบร้อยแล้ว (แทนที่การตัดสินใจเดิม)' :
                'อนุมัติการสมัครสมาชิกเรียบร้อยแล้ว และส่งอีเมลแจ้งผู้สมัครแล้ว';

            return redirect()->route('admin.approvals.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Reject a registration request.
     */
    public function reject(RegistrationApproval $approval, Request $request): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ], [
            'rejection_reason.required' => 'กรุณาระบุเหตุผลการปฏิเสธ'
        ]);

        if ($approval->status !== 'pending') {
            return redirect()->back()
                           ->with('error', 'การอนุมัติดังกล่าวได้ถูกดำเนินการแล้ว');
        }

        DB::beginTransaction();

        try {
            // Update approval record
            $approval->update([
                'status' => 'rejected',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Update user status
            $approval->user->update([
                'approval_status' => 'rejected',
            ]);

            DB::commit();

            // Refresh the model to get updated timestamps
            $approval->refresh();

            // Send rejection email
            try {
                Mail::to($approval->user->email)->send(new RegistrationRejected($approval->user, $approval));
            } catch (\Exception $e) {
                // Log email error but don't fail the rejection
                Log::error('Failed to send rejection email: ' . $e->getMessage());
            }

            return redirect()->route('admin.approvals.index')
                           ->with('success', 'ปฏิเสธการสมัครสมาชิกเรียบร้อยแล้ว และส่งอีเมลแจ้งผู้สมัครแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Bulk actions for multiple approvals.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'approval_ids' => 'required|array|min:1',
            'approval_ids.*' => 'exists:registration_approvals,id',
            'rejection_reason' => 'required_if:action,reject|string|max:1000'
        ]);

        $approvals = RegistrationApproval::with('user')
                                       ->whereIn('id', $request->approval_ids)
                                       ->where('status', 'pending')
                                       ->get();

        if ($approvals->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'ไม่พบรายการที่ต้องการดำเนินการ หรือรายการดังกล่าวได้ถูกดำเนินการแล้ว'], 400);
            }
            return redirect()->back()->with('error', 'ไม่พบรายการที่ต้องการดำเนินการ');
        }

        DB::beginTransaction();

        try {
            $successCount = 0;
            $emailErrors = [];

            foreach ($approvals as $approval) {
                if ($request->action === 'approve') {
                    // Approve the registration
                    $approval->update([
                        'status' => 'approved',
                        'reviewed_by' => Auth::id(),
                        'reviewed_at' => now(),
                    ]);

                    // Update user status
                    $approval->user->update([
                        'status' => 'active',
                        'approval_status' => 'approved',
                        'approved_at' => now(),
                    ]);

                    // Refresh model to get updated timestamps
                    $approval->refresh();

                    // Send approval email
                    try {
                        Mail::to($approval->user->email)->send(new RegistrationApproved($approval->user, $approval));
                    } catch (\Exception $e) {
                        $emailErrors[] = $approval->user->email;
                        Log::error('Failed to send bulk approval email: ' . $e->getMessage());
                    }

                } else { // reject
                    // Reject the registration
                    $approval->update([
                        'status' => 'rejected',
                        'reviewed_by' => Auth::id(),
                        'reviewed_at' => now(),
                        'rejection_reason' => $request->rejection_reason,
                    ]);

                    // Update user status
                    $approval->user->update([
                        'approval_status' => 'rejected',
                    ]);

                    // Refresh model to get updated timestamps
                    $approval->refresh();

                    // Send rejection email
                    try {
                        Mail::to($approval->user->email)->send(new RegistrationRejected($approval->user, $approval));
                    } catch (\Exception $e) {
                        $emailErrors[] = $approval->user->email;
                        Log::error('Failed to send bulk rejection email: ' . $e->getMessage());
                    }
                }

                $successCount++;
            }

            DB::commit();

            $action = $request->action === 'approve' ? 'อนุมัติ' : 'ปฏิเสธ';
            $message = "ดำเนินการ{$action} {$successCount} รายการเรียบร้อยแล้ว";
            
            if (!empty($emailErrors)) {
                $message .= " (มีปัญหาการส่งอีเมลบางรายการ)";
            } else {
                $message .= " และส่งอีเมลแจ้งผลแล้ว";
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => $message]);
            }

            return redirect()->route('admin.approvals.index')
                           ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            $errorMessage = 'เกิดข้อผิดพลาดในการดำเนินการ: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorMessage], 500);
            }
            
            return redirect()->back()->with('error', $errorMessage);
        }
    }

    /**
     * Remove the specified approval from storage.
     */
    public function destroy(RegistrationApproval $approval): RedirectResponse
    {
        // Check permissions
        if (!config('approval.super_admin.can_delete_approvals', true) && Auth::user()->role !== 'super_admin') {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ลบข้อมูลการสมัคร');
        }
        
        DB::beginTransaction();
        
        try {
            // Log deletion action
            $this->auditService->logDeletion($approval, 'Deleted by ' . Auth::user()->role);
            
            // Delete the associated user if not approved
            if ($approval->status !== 'approved') {
                $approval->user()->delete();
            }
            
            $approval->delete();
            
            DB::commit();
            
            // Redirect ตาม role ของผู้ใช้
            $redirectRoute = Auth::user()->role === 'super_admin' 
                ? 'super-admin.approvals.index' 
                : 'admin.approvals.index';
                
            return redirect()->route($redirectRoute)
                           ->with('success', 'ลบข้อมูลการสมัครเรียบร้อยแล้ว');
                           
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $e->getMessage());
        }
    }

    /**
     * Get statistics based on user role
     */
    protected function getStatistics($currentUser): array
    {
        $baseQuery = RegistrationApproval::query();
        
        // สำหรับ Admin: เฉพาะที่ตนเองดูแล + pending ที่ยังไม่มีคนดูแล
        if ($currentUser->role === 'admin' && !config('approval.admin.can_see_all_approvals', false)) {
            $baseQuery->where(function ($q) use ($currentUser) {
                $q->where('reviewed_by', null) // pending ที่ยังไม่มีคนดูแล
                  ->orWhere('reviewed_by', $currentUser->id); // หรือที่ตนเองดูแล
            });
        }
        
        return [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(), 
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'total' => (clone $baseQuery)->count(),
            'my_approvals' => RegistrationApproval::where('reviewed_by', $currentUser->id)->count(),
            'escalated' => $currentUser->role === 'super_admin' ? 
                RegistrationApproval::where('status', 'pending')
                    ->where('created_at', '<=', now()->subDays(config('approval.workflow.escalation_days', 3)))
                    ->count() : 0,
        ];
    }
}
