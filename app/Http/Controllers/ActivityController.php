<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    /**
     * แสดงรายการประวัติกิจกรรม
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // ตรวจสอบสิทธิ์การเข้าถึง
        $canViewAll = in_array($user->role, ['admin', 'super_admin']);
        
        $query = ActivityLog::with(['user']);
        
        // หาก user ทั่วไปให้ดูเฉพาะกิจกรรมของตน
        if (!$canViewAll) {
            $query->where('user_id', $user->id);
        }
        
        // กรองตามประเภทกิจกรรม
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }
        
        // กรองตามช่วงวันที่
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // กรองตามผู้ใช้ (สำหรับ admin)
        if ($canViewAll && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // กรองตาม IP Address
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }
        
        // กรองกิจกรรมที่น่าสงสัย
        if ($request->filled('suspicious') && $request->suspicious == '1') {
            $query->where('is_suspicious', true);
        }
        
        // จัดเรียงข้อมูล
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // กำหนดคอลัมน์ที่สามารถเรียงลำดับได้
        $allowedSortColumns = [
            'id', 'created_at', 'activity_type', 'ip_address', 'is_suspicious'
        ];
        
        // ตรวจสอบความถูกต้องของการเรียงลำดับ
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        // เพิ่มการเรียงลำดับตาม ID เป็นอันดับสอง เพื่อให้ผลลัพธ์สม่ำเสมอ
        $activities = $query->orderBy($sortBy, $sortOrder)
                          ->orderBy('id', 'desc')
                          ->paginate(10);
        
        // รับรายการประเภทกิจกรรมสำหรับ filter
        $activityTypes = ActivityLog::select('activity_type')
            ->distinct()
            ->orderBy('activity_type')
            ->pluck('activity_type');
        
        // รับรายการผู้ใช้สำหรับ filter (สำหรับ admin)
        $users = $canViewAll ? User::select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->get() : collect();
        
        // สถิติกิจกรรม
        $stats = $this->getActivityStats($canViewAll ? null : $user->id);
        
        return view('activities.index', compact(
            'activities', 
            'activityTypes', 
            'users', 
            'stats',
            'canViewAll'
        ));
    }
    
    /**
     * แสดงรายละเอียดกิจกรรม
     */
    public function show($id)
    {
        $user = Auth::user();
        $canViewAll = in_array($user->role, ['admin', 'super_admin']);
        
        $query = ActivityLog::with(['user']);
        
        // หาก user ทั่วไปให้ดูเฉพาะกิจกรรมของตน
        if (!$canViewAll) {
            $query->where('user_id', $user->id);
        }
        
        $activity = $query->findOrFail($id);
        
        return view('activities.show', compact('activity'));
    }
    
    /**
     * รับข้อมูลกิจกรรมผ่าน API
     */
    public function getRecentActivities(Request $request)
    {
        $user = Auth::user();
        $canViewAll = in_array($user->role, ['admin', 'super_admin']);
        
        $limit = $request->get('limit', 10);
        
        $query = ActivityLog::with(['user']);
        
        if (!$canViewAll) {
            $query->where('user_id', $user->id);
        }
        
        $activities = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'activity_type' => $activity->activity_type,
                    'friendly_description' => $activity->friendly_description,
                    'description' => $activity->description,
                    'user_name' => $activity->user ? $activity->user->name : 'System',
                    'ip_address' => $activity->ip_address,
                    'created_at' => $activity->created_at->diffForHumans(),
                    'created_at_full' => $activity->created_at->format('d/m/Y H:i:s'),
                    'is_suspicious' => $activity->is_suspicious,
                    'icon' => $activity->activity_icon,
                    'browser_info' => $activity->browser_info
                ];
            });
        
        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }
    
    /**
     * ทำเครื่องหมายกิจกรรมว่าน่าสงสัย
     */
    public function markSuspicious(Request $request, $id)
    {
        $user = Auth::user();
        
        // เฉพาะ admin และ super_admin เท่านั้นที่สามารถทำได้
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            abort(403, 'ไม่มีสิทธิ์ในการดำเนินการนี้');
        }
        
        $activity = ActivityLog::findOrFail($id);
        
        $reason = $request->input('reason', 'ทำเครื่องหมายโดย ' . $user->name);
        
        $activity->markAsSuspicious($reason);
        
        // บันทึกกิจกรรมการทำเครื่องหมาย
        ActivityLog::logActivity([
            'user_id' => $user->id,
            'activity_type' => 'security_alert',
            'description' => "ทำเครื่องหมายกิจกรรม ID #{$id} ว่าน่าสงสัย",
            'subject_type' => ActivityLog::class,
            'subject_id' => $id,
            'properties' => [
                'marked_activity_id' => $id,
                'reason' => $reason
            ]
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'ทำเครื่องหมายกิจกรรมว่าน่าสงสัยเรียบร้อยแล้ว'
        ]);
    }
    
    /**
     * ยกเลิกการทำเครื่องหมายกิจกรรมว่าน่าสงสัย
     */
    public function unmarkSuspicious($id)
    {
        $user = Auth::user();
        
        // เฉพาะ admin และ super_admin เท่านั้นที่สามารถทำได้
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            abort(403, 'ไม่มีสิทธิ์ในการดำเนินการนี้');
        }
        
        $activity = ActivityLog::findOrFail($id);
        
        $activity->update([
            'is_suspicious' => false,
            'properties' => array_merge($activity->properties ?? [], [
                'unmarked_at' => now(),
                'unmarked_by' => $user->id
            ])
        ]);
        
        // บันทึกกิจกรรมการยกเลิกเครื่องหมาย
        ActivityLog::logActivity([
            'user_id' => $user->id,
            'activity_type' => 'security_alert',
            'description' => "ยกเลิกการทำเครื่องหมายกิจกรรม ID #{$id} ว่าน่าสงสัย",
            'subject_type' => ActivityLog::class,
            'subject_id' => $id,
            'properties' => [
                'unmarked_activity_id' => $id
            ]
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'ยกเลิกการทำเครื่องหมายเรียบร้อยแล้ว'
        ]);
    }
    
    /**
     * ส่งออกข้อมูลกิจกรรม
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $canViewAll = in_array($user->role, ['admin', 'super_admin']);
        
        $query = ActivityLog::with(['user']);
        
        if (!$canViewAll) {
            $query->where('user_id', $user->id);
        }
        
        // ใช้ filter เดียวกันกับ index
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $activities = $query->orderBy('created_at', 'desc')->get();
        
        // บันทึกกิจกรรมการส่งออก
        ActivityLog::logActivity([
            'user_id' => $user->id,
            'activity_type' => 'data_export',
            'description' => 'ส่งออกข้อมูลประวัติกิจกรรม',
            'properties' => [
                'export_count' => $activities->count(),
                'filters' => $request->only(['activity_type', 'date_from', 'date_to'])
            ]
        ]);
        
        // สร้างไฟล์ CSV
        $filename = 'activity_log_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        return response()->stream(function () use ($activities) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'วันที่',
                'เวลา',
                'ผู้ใช้',
                'ประเภทกิจกรรม',
                'คำอธิบาย',
                'IP Address',
                'เบราว์เซอร์',
                'น่าสงสัย'
            ]);
            
            // Data
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->created_at->format('d/m/Y'),
                    $activity->created_at->format('H:i:s'),
                    $activity->user ? $activity->user->name : 'System',
                    $activity->friendly_description,
                    $activity->description,
                    $activity->ip_address,
                    $activity->browser_info,
                    $activity->is_suspicious ? 'ใช่' : 'ไม่'
                ]);
            }
            
            fclose($file);
        }, 200, $headers);
    }
    
    /**
     * รับสถิติกิจกรรม
     */
    private function getActivityStats($userId = null)
    {
        $baseQuery = ActivityLog::query();
        
        if ($userId) {
            $baseQuery->where('user_id', $userId);
        }
        
        $last30Days = Carbon::now()->subDays(30);
        $last7Days = Carbon::now()->subDays(7);
        $today = Carbon::today();
        
        // ใช้ query แยกกันแต่ละครั้งเพื่อป้องกันการสะสม conditions
        $totalActivities = (clone $baseQuery)->count();
        
        $activitiesLast30Days = (clone $baseQuery)
            ->where('created_at', '>=', $last30Days)
            ->count();
            
        $activitiesLast7Days = (clone $baseQuery)
            ->where('created_at', '>=', $last7Days)
            ->count();
            
        $activitiesToday = (clone $baseQuery)
            ->whereDate('created_at', $today)
            ->count();
            
        $suspiciousActivities = (clone $baseQuery)
            ->where('is_suspicious', true)
            ->count();
            
        $uniqueIps = (clone $baseQuery)
            ->distinct('ip_address')
            ->count('ip_address');
            
        $mostCommonActivity = (clone $baseQuery)
            ->select('activity_type', DB::raw('count(*) as activity_count'))
            ->groupBy('activity_type')
            ->orderByDesc('activity_count')
            ->first();
            
        $loginAttemptsToday = (clone $baseQuery)
            ->whereIn('activity_type', ['login', 'failed_login'])
            ->whereDate('created_at', $today)
            ->count();
            
        $failedLoginsToday = (clone $baseQuery)
            ->where('activity_type', 'failed_login')
            ->whereDate('created_at', $today)
            ->count();
        
        return [
            'total_activities' => $totalActivities,
            'activities_last_30_days' => $activitiesLast30Days,
            'activities_last_7_days' => $activitiesLast7Days,
            'activities_today' => $activitiesToday,
            'suspicious_activities' => $suspiciousActivities,
            'unique_ips' => $uniqueIps,
            'most_common_activity' => $mostCommonActivity,
            'login_attempts_today' => $loginAttemptsToday,
            'failed_logins_today' => $failedLoginsToday
        ];
    }
    
    /**
     * รับข้อมูลสถิติกิจกรรมแบบกราฟ
     */
    public function getChartData(Request $request)
    {
        $user = Auth::user();
        $canViewAll = in_array($user->role, ['admin', 'super_admin']);
        
        $days = $request->get('days', 7);
        $startDate = Carbon::now()->subDays($days);
        
        $baseQuery = ActivityLog::query();
        
        if (!$canViewAll) {
            $baseQuery->where('user_id', $user->id);
        }
        
        // ข้อมูลรายวัน
        $dailyData = (clone $baseQuery)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as daily_count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        // สร้างข้อมูลครบทุกวัน
        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayData = $dailyData->get($date);
            $chartData[] = [
                'date' => $date,
                'formatted_date' => Carbon::parse($date)->format('d/m'),
                'count' => $dayData ? $dayData->daily_count : 0
            ];
        }
        
        // ข้อมูลตามประเภท
        $typeData = (clone $baseQuery)
            ->selectRaw('activity_type, COUNT(*) as type_count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('activity_type')
            ->orderByDesc('type_count')
            ->limit(10)
            ->get();
        
        return response()->json([
            'success' => true,
            'daily_data' => $chartData,
            'type_data' => $typeData
        ]);
    }
}
