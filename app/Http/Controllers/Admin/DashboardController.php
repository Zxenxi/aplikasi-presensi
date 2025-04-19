<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

    class DashboardController extends Controller {
        public function index() {
            // Nanti kita akan ambil data dinamis di sini
            // $dataUntukView = [...];
            return view('admin.dashboard.index'); // Pass data nanti -> , $dataUntukView);
        }
    }