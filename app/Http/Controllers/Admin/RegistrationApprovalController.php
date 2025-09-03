<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationApproval;
use App\Models\User;
use App\Mail\RegistrationApproved;
use App\Mail\RegistrationRejected;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RegistrationApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,super_admin']);
    }

    /**
     * Display a listing of pending registration approvals.
     */
    public function index(Request $request): View
    {
        $query = RegistrationApproval::with(['user', 'reviewer'])
                                   ->orderBy('created_at', 'desc');

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

        $approvals = $query->paginate(20);

        // Statistics
        $stats = [
            'pending' => RegistrationApproval::where('status', 'pending')->count(),
            'approved' => RegistrationApproval::where('status', 'approved')->count(),
            'rejected' => RegistrationApproval::where('status', 'rejected')->count(),
            'today' => RegistrationApproval::whereDate('created_at', today())->count(),
            'total' => RegistrationApproval::count(),
        ];

        return view('admin.approvals.index', compact('approvals', 'stats'));
    }

    /**
     * Display the specified approval for detailed review.
     */
    public function show(RegistrationApproval $approval): View
    {
        $approval->load(['user', 'reviewer']);
        
        return view('admin.approvals.review', compact('approval'));
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

        DB::beginTransaction();

        try {
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

            DB::commit();

            // Send approval email
            try {
                Mail::to($approval->user->email)->send(new RegistrationApproved($approval->user, $approval));
            } catch (\Exception $e) {
                // Log email error but don't fail the approval
                Log::error('Failed to send approval email: ' . $e->getMessage());
            }

            return redirect()->route('admin.approvals.index')
                           ->with('success', 'อนุมัติการสมัครสมาชิกเรียบร้อยแล้ว และส่งอีเมลแจ้งผู้สมัครแล้ว');

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
        DB::beginTransaction();
        
        try {
            // Delete the associated user if not approved
            if ($approval->status !== 'approved') {
                $approval->user()->delete();
            }
            
            $approval->delete();
            
            DB::commit();
            
            return redirect()->route('admin.approvals.index')
                           ->with('success', 'ลบข้อมูลการสมัครเรียบร้อยแล้ว');
                           
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $e->getMessage());
        }
    }
}
