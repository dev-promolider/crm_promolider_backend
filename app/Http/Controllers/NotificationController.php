<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Get the list of notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Fetch notifications from the custom 'notifications' table
        $notifications = DB::table('notifications')
            ->where('id_receiver', $userId)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // Map generator photo if possible
        $generatorIds = $notifications->pluck('id_generator')->filter()->unique()->toArray();
        $generators = [];
        
        if (!empty($generatorIds)) {
            $generators = DB::table('users')
                ->whereIn('id', $generatorIds)
                ->pluck('photo', 'id')
                ->toArray();
        }

        $formatted = $notifications->map(function ($notif) use ($generators) {
            $photo = null;
            if ($notif->id_generator && isset($generators[$notif->id_generator])) {
                $photoPath = $generators[$notif->id_generator];
                if ($photoPath) {
                    $photo = str_starts_with($photoPath, 'http') 
                        ? $photoPath 
                        : "https://promolider-storage-user.s3.sa-east-1.amazonaws.com/{$photoPath}";
                }
            }

            return [
                'id' => $notif->id,
                'title' => $notif->title,
                'body' => $notif->body,
                'type' => $notif->type,
                'seen' => (bool)$notif->seen,
                'photo' => $photo,
                'created_at' => $notif->created_at,
                'formatted_created_at_string' => $notif->created_at ? \Carbon\Carbon::parse($notif->created_at)->setTimezone('America/Lima')->format('Y-m-d H:i') : 'N/A',
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Mark all unread notifications as seen.
     */
    public function markAsRead(Request $request)
    {
        $userId = $request->user()->id;

        DB::table('notifications')
            ->where('id_receiver', $userId)
            ->where('seen', 0)
            ->update(['seen' => 1]);

        return response()->json(['success' => true]);
    }

    /**
     * Get all notifications with filters and pagination
     */
    public function getAll(Request $request)
    {
        $userId = $request->user()->id;
        $query = DB::table('notifications')
            ->where('id_receiver', $userId);

        // Apply filters
        if ($request->filled('type') && $request->type !== 'all' && $request->type !== 'Todos') {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        // If searching by user name, we need to join with users table
        if ($request->filled('search_user')) {
            $searchTerm = '%' . $request->search_user . '%';
            $query->whereIn('id_generator', function ($q) use ($searchTerm) {
                $q->select('id')
                  ->from('users')
                  ->where('name', 'like', $searchTerm)
                  ->orWhere('last_name', 'like', $searchTerm)
                  ->orWhere('username', 'like', $searchTerm);
            });
        }

        $paginated = $query->orderBy('created_at', 'desc')->paginate(15);

        // Map generator photo for the paginated items
        $generatorIds = collect($paginated->items())->pluck('id_generator')->filter()->unique()->toArray();
        $generators = [];
        
        if (!empty($generatorIds)) {
            $generators = DB::table('users')
                ->whereIn('id', $generatorIds)
                ->pluck('photo', 'id')
                ->toArray();
        }

        $paginated->getCollection()->transform(function ($notif) use ($generators) {
            $photo = null;
            if ($notif->id_generator && isset($generators[$notif->id_generator])) {
                $photoPath = $generators[$notif->id_generator];
                if ($photoPath) {
                    $photo = str_starts_with($photoPath, 'http') 
                        ? $photoPath 
                        : "https://promolider-storage-user.s3.sa-east-1.amazonaws.com/{$photoPath}";
                }
            }

            return [
                'id' => $notif->id,
                'title' => $notif->title,
                'body' => $notif->body,
                'type' => $notif->type,
                'seen' => (bool)$notif->seen,
                'photo' => $photo,
                'created_at' => $notif->created_at,
                'formatted_created_at_string' => $notif->created_at ? \Carbon\Carbon::parse($notif->created_at)->setTimezone('America/Lima')->format('Y-m-d H:i') : 'N/A',
            ];
        });

        return response()->json($paginated);
    }
}
