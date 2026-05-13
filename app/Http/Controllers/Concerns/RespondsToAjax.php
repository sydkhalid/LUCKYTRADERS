<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RespondsToAjax
{
    protected function successResponse(Request $request, string $message, string $redirectTo): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectTo,
            ]);
        }

        return redirect($redirectTo)->with('success', $message);
    }

    protected function backSuccessResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => url()->previous(),
            ]);
        }

        return back()->with('success', $message);
    }

    protected function errorResponse(Request $request, string $message, int $status = 422): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return back()->with('error', $message)->withInput();
    }
}
