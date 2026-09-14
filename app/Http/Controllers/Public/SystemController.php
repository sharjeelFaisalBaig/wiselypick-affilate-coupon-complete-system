<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Free-tier hosts (Render's included plan among them) don't offer SSH or
 * one-off job execution, so there's no normal way to run artisan commands
 * against a live deploy. This exposes exactly one hardcoded command behind
 * a secret token — never an arbitrary command name from the request, which
 * would make this a remote-code-execution endpoint instead of an ops tool.
 */
class SystemController extends Controller
{
    public function optimizeClear(Request $request): JsonResponse
    {
        $token = config('system.deploy_token');

        if (blank($token) || ! hash_equals($token, (string) $request->query('token'))) {
            abort(403);
        }

        Artisan::call('optimize:clear');

        return response()->json([
            'ok' => true,
            'output' => trim(Artisan::output()),
        ])->withHeaders(['Cache-Control' => 'no-store']);
    }
}
