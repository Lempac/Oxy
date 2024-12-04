<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class PDFExportController extends Controller
{
    public function exportPDF()
    {
        $user = Auth::user();
        $pdf = PDF::loadView('export', compact('user'));

        return $pdf->download('user_'.$user->id.'_details.pdf');
    }
}
