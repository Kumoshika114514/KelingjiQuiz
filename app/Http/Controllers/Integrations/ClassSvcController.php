<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\QuizClass;
use App\Services\ClassModuleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassSvcController extends Controller
{
    // PATCH /integrations/classsvc/quizclass/{quizclass}/questionsets/{questionset}/toggle
    public function toggle(Request $request, int $quizclass, int $questionset, ClassModuleClient $client)
    {
        // Only allow the teacher who owns this class
        $class = QuizClass::findOrFail($quizclass);
        if ((int)$class->user_id !== (int)Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $res = $client->toggleQuestionSet($quizclass, $questionset);
        $status = $res['ok'] ? 200 : ($res['status'] ?? 400);

        // Normalize message for UI
        $msg = $res['json']['message'] ?? ($res['ok'] ? 'Status toggled.' : 'Toggle failed');
        return response()->json($res['json'] + ['message' => $msg], $status);
    }
}
