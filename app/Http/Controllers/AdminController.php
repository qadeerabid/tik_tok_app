<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;

class AdminController extends Controller
{
    public function dashboard()
    {
        $videos = Video::all();
        return view('admin.dashboard', compact('videos'));
    }

    public function manageVideos()
    {
        $videos = Video::all();
        return view('admin.manage_videos', compact('videos'));
    }
}

