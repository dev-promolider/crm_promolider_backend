<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Helpers\ParseUrl;
use App\Models\Category;
use App\Models\Preferences;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VcrPreferencesController extends Controller
{
    /**
     * GET /api/v1/preferences/show-preferences
     */
    public function showPreferences()
    {
        $userId = auth()->id();
        $preferences = Preferences::join('categories', 'categories.id', '=', 'preferences.categories_id')
            ->where('preferences.user_id', $userId)
            ->select('preferences.id', 'preferences.categories_id', 'categories.name', 'categories.icon')
            ->get();

        foreach ($preferences as $pref) {
            $pref->icon = ParseUrl::contacAtrrS3($pref->icon);
        }

        return response()->json($preferences);
    }

    /**
     * GET /api/v1/category/list
     */
    public function categoryList()
    {
        $categories = Category::select('id', 'name', 'icon')->get();
        foreach ($categories as $cat) {
            $cat->icon = ParseUrl::contacAtrrS3($cat->icon);
        }

        $user = User::select('id', 'name', 'status_preference')->find(auth()->id());

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $categories,
            'data2' => $user,
        ]);
    }

    /**
     * POST /api/v1/preferences/add
     */
    public function addPreferences(Request $request)
    {
        $userId = auth()->id();
        $categories = $request->input('categorys', []);

        foreach ($categories as $catId) {
            $exists = Preferences::where('categories_id', $catId)->where('user_id', $userId)->exists();
            if (!$exists) {
                Preferences::create([
                    'user_id' => $userId,
                    'categories_id' => $catId,
                ]);
            }
        }

        User::where('id', $userId)->update(['status_preference' => 1]);

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => 'saved preferences',
        ]);
    }

    /**
     * POST /api/v1/preferences/update/{id}
     */
    public function updatePreference($id)
    {
        $userId = auth()->id();
        $exists = Preferences::where('categories_id', $id)->where('user_id', $userId)->exists();

        if (!$exists) {
            Preferences::create([
                'user_id' => $userId,
                'categories_id' => $id,
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => 'preference saved',
        ]);
    }

    /**
     * POST /api/v1/preferences/delete/{id}
     */
    public function deletePreference($id)
    {
        $userId = auth()->id();
        Preferences::where('categories_id', $id)->where('user_id', $userId)->delete();

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => 'preference deleted',
        ]);
    }

    /**
     * POST /api/v1/preferences/delete-preferences
     */
    public function deleteMultiplePreferences(Request $request)
    {
        $userId = auth()->id();
        $categories = $request->input('categorys', []);

        Preferences::where('user_id', $userId)->whereIn('categories_id', $categories)->delete();

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => 'preferences deleted',
        ]);
    }
}
