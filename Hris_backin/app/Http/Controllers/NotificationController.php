<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the authenticated user.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        // Determine app_id from route context
        $appId = $this->getAppIdFromRoute($request);

        $query = Notification::where('user_id', $user->id);

        // Always filter by app_id to ensure users only see notifications for their current app context
        if ($appId) {
            $query->where('app_id', $appId);
        }

        // Apply filters
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'unread':
                    $query->unread();
                    break;
                case 'read':
                    $query->read();
                    break;
            }
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        // Determine the template based on the route prefix
        $routePrefix = $request->route()->getPrefix();
        $template = 'Notifications/Index'; // Default

        if (str_contains($routePrefix, 'admin')) {
            $template = 'Admin/Notifications/Index';
        } elseif (str_contains($routePrefix, 'frls')) {
            $template = 'FRMS/Notifications/Index';
        } elseif (str_contains($routePrefix, 'tracking')) {
            $template = 'Tracking/Notifications/Index';
        }

        return Inertia::render($template, [
            'notifications' => $notifications
        ]);
    }

    /**
     * Get unread notifications count for the authenticated user.
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Determine app_id from route context
        $appId = $this->getAppIdFromRoute($request);

        $query = Notification::where('user_id', $user->id);

        // Always filter by app_id to ensure count is for current app context only
        if ($appId) {
            $query->where('app_id', $appId);
        }

        $count = $query->unread()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for dropdown display.
     */
    public function getRecent(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Start with base query filtering by user_id
        $query = Notification::where('user_id', $user->id);

        // Determine app_id from route context or request parameter
        $appId = $this->getAppIdFromRoute($request);

        // If app_id is provided in request parameter, use it
        if ($request->has('app_id') && $request->app_id) {
            $appId = $request->app_id;
        }

        // Filter by app_id if available
        if ($appId) {
            $query->where('app_id', $appId);
        }
        // If no app_id is specified, show notifications from all apps for this user
        // This allows flexibility while still maintaining user-level security

        $notifications = $query->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Display the specified notification.
     */
    public function show(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Ensure the notification belongs to the authenticated user
        $hasAccess = $notification->user_id === $user->id;

        if (!$hasAccess) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['notification' => $notification]);
    }

    /**
     * Mark the specified notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Ensure the notification belongs to the authenticated user
        $hasAccess = $notification->user_id === $user->id;

        if (!$hasAccess) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification->fresh()
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Determine app_id from route context
        $appId = $this->getAppIdFromRoute($request);

        $query = Notification::where('user_id', $user->id);

        // Always filter by app_id to ensure we only mark notifications for current app context
        if ($appId) {
            $query->where('app_id', $appId);
        }

        $updated = $query->unread()->update(['is_read' => true]);

        return response()->json([
            'message' => 'All notifications marked as read',
            'updated_count' => $updated
        ]);
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Ensure the notification belongs to the authenticated user
        $hasAccess = $notification->user_id === $user->id;

        if (!$hasAccess) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }

    /**
     * Determine app_id from the current route context.
     * This method extracts the app_id based on the route prefix, similar to how
     * AdminController's getUserMenusData method works with app context.
     */
    private function getAppIdFromRoute(Request $request): ?int
    {
        $routeName = $request->route()->getName();
        $routeUri = $request->route()->uri();
        $routePrefix = $request->route()->getPrefix();

        // First try to get app_id from route name prefix
        if ($routeName) {
            if (str_starts_with($routeName, 'admin.')) {
                return 1; // Admin app
            }
            if (str_starts_with($routeName, 'hris.')) {
                return 2; // HRIS app
            }
            if (str_starts_with($routeName, 'tracking.')) {
                return 3; // Tracking app
            }
            if (str_starts_with($routeName, 'frls.')) {
                return 4; // FRMS app
            }
        }

        // Fallback to route prefix if available
        if ($routePrefix) {
            $appMapping = [
                'admin' => 1,      // Admin app
                'hris' => 2,       // HRIS app
                'tracking' => 3,   // Tracking app
                'frls' => 4,       // FRMS app
            ];

            foreach ($appMapping as $prefix => $appId) {
                if (str_starts_with($routePrefix, $prefix)) {
                    return $appId;
                }
            }
        }

        // Final fallback: check route URI for app context
        if ($routeUri) {
            if (str_starts_with($routeUri, 'admin/')) {
                return 1; // Admin app
            }
            if (str_starts_with($routeUri, 'hris/')) {
                return 2; // HRIS app
            }
            if (str_starts_with($routeUri, 'tracking/')) {
                return 3; // Tracking app
            }
            if (str_starts_with($routeUri, 'frls/')) {
                return 4; // FRMS app
            }
        }

        return null; // Default case - no app context
    }
}
