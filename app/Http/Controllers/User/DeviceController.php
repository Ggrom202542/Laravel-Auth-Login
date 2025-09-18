<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserDevice;

class DeviceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:user,admin,super_admin']);
    }

    /**
     * Display a listing of user devices.
     */
    public function index()
    {
        $user = Auth::user();
        
        $userDevices = UserDevice::where('user_id', $user->id)
                                ->orderBy('last_seen_at', 'desc')
                                ->paginate(10);

        $deviceStats = [
            'total_devices' => UserDevice::where('user_id', $user->id)->count(),
            'trusted_devices' => UserDevice::where('user_id', $user->id)->where('is_trusted', true)->count(),
            'active_devices' => UserDevice::where('user_id', $user->id)->where('is_active', true)->count()
        ];

        return view('user.devices.index', compact('userDevices', 'deviceStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $device = UserDevice::where('user_id', $user->id)->findOrFail($id);
        
        // If it's an AJAX request (for modal), return partial view
        if (request()->ajax()) {
            return view('user.devices.show', compact('device'))->render();
        }
        
        return view('user.devices.show', compact('device'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $device = UserDevice::where('user_id', $user->id)->findOrFail($id);
        
        $device->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Device removed successfully'
        ]);
    }

    /**
     * Trust or untrust a device
     */
    public function toggleTrust(Request $request, string $id)
    {
        $user = Auth::user();
        $device = UserDevice::where('user_id', $user->id)->findOrFail($id);
        
        $device->is_trusted = $request->input('trusted', false);
        $device->save();
        
        return response()->json([
            'success' => true,
            'message' => $device->is_trusted ? 'Device trusted successfully' : 'Device trust removed successfully'
        ]);
    }

    /**
     * Trust current device (auto-detect current device)
     */
    public function trustCurrentDevice(Request $request)
    {
        $user = Auth::user();
        $userAgent = $request->header('User-Agent');
        $ipAddress = $request->ip();
        
        // Try to find current device by IP and User Agent
        $currentDevice = UserDevice::where('user_id', $user->id)
            ->where('ip_address', $ipAddress)
            ->where('user_agent', $userAgent)
            ->first();
        
        if (!$currentDevice) {
            // If device not found, create a new one
            $currentDevice = UserDevice::create([
                'user_id' => $user->id,
                'device_name' => $this->getDeviceName($userAgent),
                'device_type' => $this->getDeviceType($userAgent),
                'browser_name' => $this->getBrowserName($userAgent),
                'browser_version' => $this->getBrowserVersion($userAgent),
                'operating_system' => $this->getOperatingSystem($userAgent),
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
                'is_trusted' => true,
                'is_active' => true,
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'login_count' => 1
            ]);
        } else {
            // If device exists, just mark as trusted
            $currentDevice->is_trusted = true;
            $currentDevice->last_seen_at = now();
            $currentDevice->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Current device has been trusted successfully',
            'device' => $currentDevice
        ]);
    }

    /**
     * Register current device (similar to trust but for tracking purposes)
     */
    public function registerCurrentDevice(Request $request)
    {
        $user = Auth::user();
        $userAgent = $request->header('User-Agent');
        $ipAddress = $request->ip();
        
        // Check if device already exists
        $existingDevice = UserDevice::where('user_id', $user->id)
            ->where('ip_address', $ipAddress)
            ->where('user_agent', $userAgent)
            ->first();
        
        if ($existingDevice) {
            return response()->json([
                'success' => false,
                'message' => 'Device is already registered'
            ]);
        }
        
        // Create new device registration
        $newDevice = UserDevice::create([
            'user_id' => $user->id,
            'device_name' => $this->getDeviceName($userAgent),
            'device_type' => $this->getDeviceType($userAgent),
            'browser_name' => $this->getBrowserName($userAgent),
            'browser_version' => $this->getBrowserVersion($userAgent),
            'operating_system' => $this->getOperatingSystem($userAgent),
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'is_trusted' => false, // Not trusted by default for security
            'is_active' => true,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'login_count' => 1,
            'requires_verification' => true
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully for enhanced security tracking',
            'device' => $newDevice
        ]);
    }

    /**
     * Helper methods for device detection
     */
    private function getDeviceName($userAgent)
    {
        // Simple device name detection
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPhone/', $userAgent)) {
                return 'iPhone';
            } elseif (preg_match('/iPad/', $userAgent)) {
                return 'iPad';
            } elseif (preg_match('/Android/', $userAgent)) {
                return 'Android Device';
            }
            return 'Mobile Device';
        }
        return 'Desktop Computer';
    }

    private function getDeviceType($userAgent)
    {
        if (preg_match('/Mobile|iPhone|Android/', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/iPad|Tablet/', $userAgent)) {
            return 'tablet';
        }
        return 'desktop';
    }

    private function getBrowserName($userAgent)
    {
        if (preg_match('/Chrome/', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Edge/', $userAgent)) {
            return 'Edge';
        }
        return 'Unknown Browser';
    }

    private function getBrowserVersion($userAgent)
    {
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            return $matches[1];
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            return $matches[1];
        } elseif (preg_match('/Version\/([0-9.]+).*Safari/', $userAgent, $matches)) {
            return $matches[1];
        }
        return 'Unknown';
    }

    private function getOperatingSystem($userAgent)
    {
        if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
            return 'Windows ' . $matches[1];
        } elseif (preg_match('/Mac OS X ([0-9_]+)/', $userAgent, $matches)) {
            return 'macOS ' . str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Android ([0-9.]+)/', $userAgent, $matches)) {
            return 'Android ' . $matches[1];
        } elseif (preg_match('/iPhone OS ([0-9_]+)/', $userAgent, $matches)) {
            return 'iOS ' . str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Linux/', $userAgent)) {
            return 'Linux';
        }
        return 'Unknown OS';
    }

    /**
     * Untrust a specific device
     */
    public function untrustDevice(UserDevice $device, Request $request)
    {
        // Ensure user can only untrust their own devices
        if ($device->user_id !== auth()->id()) {
            abort(403);
        }
        
        $device->update(['is_trusted' => false]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Device untrusted successfully'
            ]);
        }
        
        return redirect()->route('user.devices.index')
                       ->with('success', 'Device untrusted successfully.');
    }
    
    /**
     * Logout from all devices except current
     */
    public function logoutAllDevices(Request $request)
    {
        $user = auth()->user();
        $currentUserAgent = $request->header('User-Agent');
        $currentIp = $request->ip();
        
        // Delete all sessions except current
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();
        
        // Mark all other devices as logged out by updating last_seen_at
        UserDevice::where('user_id', $user->id)
                  ->where(function($query) use ($currentUserAgent, $currentIp) {
                      $query->where('user_agent', '!=', $currentUserAgent)
                            ->orWhere('ip_address', '!=', $currentIp);
                  })
                  ->update([
                      'last_seen_at' => now(),
                      'is_active' => false
                  ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out from all other devices successfully'
            ]);
        }
        
        return redirect()->route('user.devices.index')
                       ->with('success', 'Logged out from all other devices successfully.');
    }
}