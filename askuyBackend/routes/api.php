<?php

use App\Http\Controllers\AiWeaknessMappingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamSessionController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\FocusSessionController;
use App\Http\Controllers\GradeUploadController;
use App\Http\Controllers\PracticePackageController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\RpaJobController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/google', [AuthController::class, 'google']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// RPA (UiPath) Integration Routes - dilindungi middleware X-RPA-Key
Route::prefix('rpa')->middleware('rpa')->group(function () {
    Route::post('/jobs/claim', [RpaJobController::class, 'claim']);
    Route::get('/jobs/pending', [RpaJobController::class, 'pending']);
    Route::post('/jobs/{practicePackage}/complete', [RpaJobController::class, 'complete']);
    Route::post('/jobs/{practicePackage}/fail', [RpaJobController::class, 'fail']);
    Route::post('/jobs/{practicePackage}/upload-pdf', [RpaJobController::class, 'uploadPdf']);
});

// Protected API Routes (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // 1. Subjects (Read: all, Write: instructor/admin)
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/{subject}', [SubjectController::class, 'show']);
    Route::middleware('role:instructor,admin')->group(function () {
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::put('/subjects/{subject}', [SubjectController::class, 'update']);
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);
    });

    // 2. Questions (Read: all, Write: instructor/admin)
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::get('/questions/{question}', [QuestionController::class, 'show']);
    Route::middleware('role:instructor,admin')->group(function () {
        Route::post('/questions', [QuestionController::class, 'store']);
        Route::put('/questions/{question}', [QuestionController::class, 'update']);
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);
    });

    // 3. Flashcards (Read: all, Write: instructor/admin)
    Route::get('/flashcards', [FlashcardController::class, 'index']);
    Route::get('/flashcards/{flashcard}', [FlashcardController::class, 'show']);
    Route::middleware('role:instructor,admin')->group(function () {
        Route::post('/flashcards', [FlashcardController::class, 'store']);
        Route::put('/flashcards/{flashcard}', [FlashcardController::class, 'update']);
        Route::delete('/flashcards/{flashcard}', [FlashcardController::class, 'destroy']);
    });

    // Flashcard Progress (User specific)
    Route::post('/flashcards/{flashcard}/progress', [FlashcardController::class, 'updateProgress']);
    Route::get('/flashcard-progress', [FlashcardController::class, 'myProgress']);

    // 4. Focus Sessions (User specific)
    Route::get('/focus-sessions', [FocusSessionController::class, 'index']);
    Route::post('/focus-sessions', [FocusSessionController::class, 'store']);
    Route::get('/focus-sessions/{focusSession}', [FocusSessionController::class, 'show']);
    Route::delete('/focus-sessions/{focusSession}', [FocusSessionController::class, 'destroy']);

    // 5. Chat Sessions & Messages (User specific)
    Route::get('/chat-sessions', [ChatSessionController::class, 'index']);
    Route::post('/chat-sessions', [ChatSessionController::class, 'store']);
    Route::get('/chat-sessions/{chatSession}', [ChatSessionController::class, 'show']);
    Route::put('/chat-sessions/{chatSession}', [ChatSessionController::class, 'update']);
    Route::delete('/chat-sessions/{chatSession}', [ChatSessionController::class, 'destroy']);
    Route::post('/chat-sessions/{chatSession}/messages', [ChatSessionController::class, 'storeMessage']);

    // 6. Practice Packages
    Route::get('/practice-packages', [PracticePackageController::class, 'index']);
    Route::post('/practice-packages', [PracticePackageController::class, 'store']);
    Route::get('/practice-packages/{practicePackage}', [PracticePackageController::class, 'show']);
    Route::get('/practice-packages/{practicePackage}/download', [PracticePackageController::class, 'download']);

    // 7. Exam Sessions & Answers
    Route::get('/exam-sessions', [ExamSessionController::class, 'index']);
    Route::post('/exam-sessions', [ExamSessionController::class, 'store']);
    Route::get('/exam-sessions/{examSession}', [ExamSessionController::class, 'show']);
    Route::get('/exam-sessions/{examSession}/remedial', [ExamSessionController::class, 'remedial']);

    // 8. AI Weakness Mappings
    Route::get('/ai-weakness-mappings', [AiWeaknessMappingController::class, 'index']);

    // 9. Grade Uploads
    Route::get('/grade-uploads', [GradeUploadController::class, 'index']);
    Route::post('/grade-uploads', [GradeUploadController::class, 'store']);
    Route::get('/grade-uploads/{gradeUpload}', [GradeUploadController::class, 'show']);

});
