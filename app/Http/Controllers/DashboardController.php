<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuizClass;

class DashboardController extends Controller
{
    public function index(){
        $user = auth()->user(); 
        $quizClasses = $user->quizClasses()->get();

        return view("dashboard",compact("quizClasses"));
    }
}
