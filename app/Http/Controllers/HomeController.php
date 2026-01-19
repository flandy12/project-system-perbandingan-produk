<?php

namespace App\Http\Controllers;

use App\Models\HeadlineSlide;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $headline = HeadlineSlide::all();
        return view('pages.home.index',compact('headline'));
    }
}
