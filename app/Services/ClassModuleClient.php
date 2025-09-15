<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClassModuleClient
{
    protected string $base;
    protected string $token;

    public function __construct()
    {
        $this->base  = rtrim(config('services.class_svc.base_url', env('CLASS_SVC_BASE', 'http://localhost')), '/');
        $this->token = config('services.class_svc.token', env('CLASS_SVC_TOKEN', ''));
    }

    public function toggleQuestionSet(int $quizClassId, int $questionSetId): array
    {
        $res = Http::baseUrl($this->base)
            ->timeout(8)
            ->withToken($this->token)
            ->patch("/api/teacher/quizclass/{$quizClassId}/questionsets/{$questionSetId}/toggle");

        return ['ok' => $res->successful(), 'json' => $res->json(), 'status' => $res->status()];
    }
}
