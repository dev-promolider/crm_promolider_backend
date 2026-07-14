<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use App\Models\CommentDynamic;
use Promolider\Domain\Marketing\Ports\Out\GameCommentRepositoryInterface;
use Illuminate\Support\Facades\Log;

class EloquentGameCommentRepository implements GameCommentRepositoryInterface
{
    public function listByGame(int $courseGameId): array
    {
        return CommentDynamic::where('id_course_games', $courseGameId)
            ->with(['author' => function ($query) {
                $query->select('id', 'username', 'photo');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'id_author' => $comment->id_author,
                    'id_course_games' => $comment->id_course_games,
                    'content' => $comment->content,
                    'username' => $comment->author->username ?? null,
                    'photo' => $comment->author->photo ?? null,
                    'created_at' => $comment->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    public function create(array $data): array
    {
        try {
            $comment = CommentDynamic::create([
                'id_author' => $data['id_author'],
                'id_course_games' => $data['id_course_games'],
                'content' => $data['content'],
            ]);

            $comment->load('author:id,username,photo');

            return [
                'id' => $comment->id,
                'id_author' => $comment->id_author,
                'id_course_games' => $comment->id_course_games,
                'content' => $comment->content,
                'username' => $comment->author->username ?? null,
                'photo' => $comment->author->photo ?? null,
                'created_at' => $comment->created_at->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('Error creating game comment: ' . $e->getMessage());
            throw $e;
        }
    }
}
