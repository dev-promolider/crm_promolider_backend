<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = Wishlist::with('course')
            ->where('user_id', $request->user()->id)
            ->get()
            ->map(function($wishlist) {
                // Return structured data for the frontend
                $course = $wishlist->course;
                if (!$course) return null;
                
                return [
                    'id' => $course->id,
                    'title' => $course->title ?? $course->name ?? 'Curso',
                    'price' => (float)($course->price_with_discount > 0 ? $course->price_with_discount : ($course->price ?? $course->precio ?? 0)),
                    'originalPrice' => (float)($course->precio ?? $course->price ?? 0),
                    'url_portada' => $course->url_portada ?? $course->img ?? $course->coverUrl ?? '',
                    'category' => $course->categoria ?? $course->category ?? 'Curso',
                    'slug' => $course->slug ?? '',
                ];
            })
            ->filter();
            
        return response()->json($wishlists->values());
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'course_id' => $request->course_id,
        ]);

        return response()->json([
            'message' => 'Añadido a lista de deseos'
        ]);
    }

    public function destroy(Request $request, $course_id)
    {
        Wishlist::where('user_id', $request->user()->id)
            ->where('course_id', $course_id)
            ->delete();

        return response()->json(['message' => 'Eliminado de lista de deseos']);
    }
}
