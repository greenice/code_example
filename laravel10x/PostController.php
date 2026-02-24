<?php

namespace App\Http\Controllers;

use App\Dto\Post\IndexDto;
use App\Dto\Post\SaveDto;
use App\Http\Requests\Post\IndexRequest;
use App\Http\Requests\Post\SaveRequest;
use App\Http\Resources\PostResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService,
    ) {}

    public function index(IndexRequest $request, Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorize('view', $lesson);

        $posts = $this->postService->getPaginatedCoursePosts(
            IndexDto::fromRequest($request),
            $course,
        );

        $posts = Auth::user()->attachFavoriteStatus($posts);

        return response()->json([
            'posts' => PostResource::collection($posts),
            'postsTotal' => $posts->total(),
            'queryParams' => $request->query(),
        ]);
    }

    public function store(SaveRequest $request, Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorize('view', $lesson);

        $post = $this->postService->store(
            SaveDto::fromRequest($request),
            $course,
            $lesson,
            Auth::user(),
        );

        return response()->json([
            'status' => __('Post has been added.'),
            'post' => PostResource::make($post->load(['user'])),
        ]);
    }

    public function update(SaveRequest $request, Course $course, Lesson $lesson, Post $post): JsonResponse
    {
        $this->authorize('edit', $post);

        $post = $this->postService->update(
            SaveDto::fromRequest($request),
            $post,
        );

        return response()->json([
            'status' => __('Post has been updated.'),
            'post' => PostResource::make($post->unsetRelations()),
        ]);
    }

    public function destroy(Course $course, Lesson $lesson, Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'status' => __('Post has been removed.'),
        ]);
    }
    
} 
