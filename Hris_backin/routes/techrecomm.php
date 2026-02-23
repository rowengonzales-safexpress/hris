<?php

use App\Http\Controllers\IT\TechRecommController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/tech-recommendation', [TechRecommController::class, 'index'])->name('tech-recommendation.index');
    Route::get('/tech-recommendation/create', [TechRecommController::class, 'create'])->name('tech-recommendation.create');
    Route::post('/tech-recommendation', [TechRecommController::class, 'store'])->name('tech-recommendation.store');
    Route::get('/tech-recommendation/{id}', [TechRecommController::class, 'show'])->name('tech-recommendation.show');
    Route::get('/tech-recommendation/{id}/edit', [TechRecommController::class, 'edit'])->name('tech-recommendation.edit');
    Route::put('/tech-recommendation/{id}', [TechRecommController::class, 'update'])->name('tech-recommendation.update');
    Route::delete('/tech-recommendation/{id}', [TechRecommController::class, 'destroy'])->name('tech-recommendation.destroy');
});