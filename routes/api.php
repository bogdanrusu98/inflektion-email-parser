<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\EmailController;

Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    return $user->createToken('api-token')->plainTextToken;
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/store', [EmailController::class, 'store']);
    Route::get('/', [EmailController::class, 'index']);
    Route::delete('/{id}', [EmailController::class, 'destroy']);
    Route::put('/{id}', [EmailController::class, 'update']);
    Route::get('/{id}', [EmailController::class, 'show']);
});
