<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function export($filename) 
    {
        return response()->download(Storage::path($filename))->deleteFileAfterSend(true);
    }
    public function exportReport($filename) 
    {
        return response()->download(Storage::path($filename))->deleteFileAfterSend(true);
    }
}
