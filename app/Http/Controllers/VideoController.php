<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Comment;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    public function index()
    {
        try {
            $videos = Video::where('user_id', auth()->id())->get();
            return view('creator.videos.index', compact('videos'));
        } catch (\Exception $e) {
            Log::error('Error fetching creator videos: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Unable to fetch your videos. Please try again later.']);
        }
    }

    public function publicIndex()
    {
        try {
            $videos = Video::withCount('likes', 'comments')->get();
            return view('videos.index', compact('videos'));
        } catch (\Exception $e) {
            Log::error('Error fetching public videos: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Unable to fetch videos. Please try again later.']);
        }
    }
    public function getVideoData($videoId)
    {
        // return $videoId;
        $video = Video::withCount('likes', 'comments')->findOrFail($videoId);
        return response()->json([
            'likes_count' => $video->likes_count,
            'comments_count' => $video->comments_count,
        ]);
    }

    public function create()
    {
        return view('creator.videos.create');
    }

    public function store(Request $request)
    {
        try {
            // Validate the input
            $request->validate([
                'title' => 'required',
                'file' => 'required|file|mimes:mp4,avi,mkv|max:20480', // 20MB limit
            ]);

            // Define the path where the file will be stored
            $destinationPath = public_path('videos');

            // Get the uploaded file and generate a unique filename
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Move the file to the destination path
            $file->move($destinationPath, $fileName);

            // Save the video information in the database
            Video::create([
                'title' => $request->title,
                'file_path' => 'videos/' . $fileName,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('videos.index')->with('success', 'Video uploaded successfully.');
        } catch (\Exception $e) {
            Log::error('Error storing video: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to upload video. Please try again.']);
        }
    }


    public function destroy(Video $video)
    {
        try {
            $this->authorize('delete', $video);

            $video->delete();
            return redirect()->route('videos.index')->with('success', 'Video deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting video: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete video. Please try again later.']);
        }
    }

    public function addComment(Request $request, $videoId)
    {
        $request->validate(['comment' => 'required|string']);
        $video = Video::findOrFail($videoId);

        $video->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Comment added successfully.']);
    }

    public function toggleLike($videoId)
    {
        $video = Video::findOrFail($videoId);
        $like = $video->likes()->where('user_id', auth()->id())->first();

        if ($like) {
            $like->delete();
            return response()->json(['message' => 'Like removed.']);
        } else {
            $video->likes()->create(['user_id' => auth()->id()]);
            return response()->json(['message' => 'Video liked.']);
        }
    }
    // public function dislike(Video $video)
    // {
    //     $user = auth()->user();

    //     if (!$video->isLikedByUser($user->id)) {
    //         return response()->json(['message' => 'You haven\'t liked this video yet.'], 400);
    //     }

    //     $video->likes()->where('user_id', $user->id)->delete();

    //     return response()->json(['message' => 'You disliked the video.']);
    // }

    public function addRating(Request $request, Video $video)
    {
        try {
            $request->validate(['rating' => 'required|integer|between:1,5']);

            $video->ratings()->create([
                'user_id' => Auth::id(),
                'rating' => $request->rating,
            ]);

            return redirect()->route('videos.show', $video)->with('success', 'Rating added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding rating: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to add rating. Please try again.']);
        }
    }
}
