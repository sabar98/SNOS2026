<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Admin\CertificateTemplateController;
use App\Http\Controllers\Admin\CheckInController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\JournalController as AdminJournalController;
use App\Http\Controllers\Admin\LandingSettingController;
use App\Http\Controllers\Admin\ModeratorController as AdminModeratorController;
use App\Http\Controllers\Admin\NarasumberController as AdminNarasumberController;
use App\Http\Controllers\Admin\ParticipantController as AdminParticipantController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PimpinanController as AdminPimpinanController;
use App\Http\Controllers\Admin\PresentationSlotController;
use App\Http\Controllers\Admin\PublicationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewerAssignmentController;
use App\Http\Controllers\Admin\ReviewerController as AdminReviewerController;
use App\Http\Controllers\Admin\ScheduleSessionController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Leadership\StatisticsController;
use App\Http\Controllers\Moderator\SessionController as ModeratorSessionController;
use App\Http\Controllers\Narasumber\DashboardController as NarasumberDashboardController;
use App\Http\Controllers\Participant\AnnouncementController as ParticipantAnnouncementController;
use App\Http\Controllers\Participant\ArticleController as ParticipantArticleController;
use App\Http\Controllers\Participant\ArticleRevisionController;
use App\Http\Controllers\Participant\AttendanceController as ParticipantAttendanceController;
use App\Http\Controllers\Participant\CertificateController as ParticipantCertificateController;
use App\Http\Controllers\Participant\DashboardController as ParticipantDashboardController;
use App\Http\Controllers\Participant\EvaluationController;
use App\Http\Controllers\Participant\EventRegistrationController;
use App\Http\Controllers\Participant\PaymentController as ParticipantPaymentController;
use App\Http\Controllers\Participant\PresentationMaterialController;
use App\Http\Controllers\Participant\ProfileController as ParticipantProfileController;
use App\Http\Controllers\Participant\ScheduleController as ParticipantScheduleController;
use App\Http\Controllers\Reviewer\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('certificates/verify/{token}', [CertificateVerificationController::class, 'show'])->name('certificates.verify');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Peserta / Presenter
    Route::middleware('role:peserta')->prefix('participant')->name('participant.')->group(function () {
        Route::get('dashboard', [ParticipantDashboardController::class, 'index'])->name('dashboard');

        Route::get('profile', [ParticipantProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ParticipantProfileController::class, 'update'])->name('profile.update');

        Route::get('registrations/create', [EventRegistrationController::class, 'create'])->name('registrations.create');
        Route::post('registrations', [EventRegistrationController::class, 'store'])->name('registrations.store');
        Route::get('registrations/{registration}', [EventRegistrationController::class, 'show'])->name('registrations.show');
        Route::get('registrations/{registration}/edit', [EventRegistrationController::class, 'edit'])->name('registrations.edit');
        Route::put('registrations/{registration}', [EventRegistrationController::class, 'update'])->name('registrations.update');

        Route::post('payments/{payment}/proof', [ParticipantPaymentController::class, 'uploadProof'])->name('payments.upload');

        Route::get('registrations/{registration}/articles/create', [ParticipantArticleController::class, 'create'])->name('articles.create');
        Route::post('registrations/{registration}/articles', [ParticipantArticleController::class, 'store'])->name('articles.store');
        Route::get('articles/{article}', [ParticipantArticleController::class, 'show'])->name('articles.show');
        Route::post('articles/{article}/revisions', [ArticleRevisionController::class, 'store'])->name('articles.revisions.store');
        Route::post('articles/{article}/presentation-material', [PresentationMaterialController::class, 'store'])->name('articles.presentation-material.store');

        Route::get('schedule', [ParticipantScheduleController::class, 'index'])->name('schedule');
        Route::post('check-in', [ParticipantAttendanceController::class, 'store'])->name('check-in');
        Route::post('sessions/{scheduleSession}/attendance', [ParticipantAttendanceController::class, 'storeSession'])->name('sessions.attendance');
        Route::post('registrations/{registration}/evaluation', [EvaluationController::class, 'store'])->name('evaluation.store');
        Route::get('certificates', [ParticipantCertificateController::class, 'index'])->name('certificates');
        Route::get('announcements', [ParticipantAnnouncementController::class, 'index'])->name('announcements');
    });

    // Admin / Panitia
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('participants', [AdminParticipantController::class, 'index'])->name('participants.index');
        Route::get('participants/{registration}', [AdminParticipantController::class, 'show'])->name('participants.show');

        Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::put('payments/{payment}', [AdminPaymentController::class, 'update'])->name('payments.update');

        Route::get('journals', [AdminJournalController::class, 'index'])->name('journals.index');
        Route::post('journals', [AdminJournalController::class, 'store'])->name('journals.store');
        Route::put('journals/{journal}', [AdminJournalController::class, 'update'])->name('journals.update');
        Route::delete('journals/{journal}', [AdminJournalController::class, 'destroy'])->name('journals.destroy');

        Route::get('bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts.index');
        Route::post('bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
        Route::put('bank-accounts/{bankAccount}', [BankAccountController::class, 'update'])->name('bank-accounts.update');
        Route::delete('bank-accounts/{bankAccount}', [BankAccountController::class, 'destroy'])->name('bank-accounts.destroy');

        Route::get('articles', [AdminArticleController::class, 'index'])->name('articles.index');
        Route::get('articles/{article}', [AdminArticleController::class, 'show'])->name('articles.show');
        Route::put('articles/{article}', [AdminArticleController::class, 'update'])->name('articles.update');
        Route::post('articles/{article}/loa', [AdminArticleController::class, 'issueLoa'])->name('articles.loa');

        Route::get('publications', [PublicationController::class, 'index'])->name('publications.index');
        Route::post('publications/{article}', [PublicationController::class, 'store'])->name('publications.store');

        Route::get('check-in', [CheckInController::class, 'index'])->name('check-in.index');
        Route::post('check-in', [CheckInController::class, 'store'])->name('check-in.store');

        Route::get('reviewer-assignments', [ReviewerAssignmentController::class, 'index'])->name('reviewer-assignments.index');
        Route::post('reviewer-assignments', [ReviewerAssignmentController::class, 'store'])->name('reviewer-assignments.store');
        Route::put('reviewer-assignments/{reviewerAssignment}', [ReviewerAssignmentController::class, 'update'])->name('reviewer-assignments.update');
        Route::delete('reviewer-assignments/{reviewerAssignment}', [ReviewerAssignmentController::class, 'destroy'])->name('reviewer-assignments.destroy');

        Route::get('moderators', [AdminModeratorController::class, 'index'])->name('moderators.index');
        Route::get('moderators/create', [AdminModeratorController::class, 'create'])->name('moderators.create');
        Route::post('moderators', [AdminModeratorController::class, 'store'])->name('moderators.store');
        Route::get('moderators/{moderator}/edit', [AdminModeratorController::class, 'edit'])->name('moderators.edit');
        Route::put('moderators/{moderator}', [AdminModeratorController::class, 'update'])->name('moderators.update');
        Route::delete('moderators/{moderator}', [AdminModeratorController::class, 'destroy'])->name('moderators.destroy');

        Route::get('reviewers', [AdminReviewerController::class, 'index'])->name('reviewers.index');
        Route::get('reviewers/create', [AdminReviewerController::class, 'create'])->name('reviewers.create');
        Route::post('reviewers', [AdminReviewerController::class, 'store'])->name('reviewers.store');
        Route::get('reviewers/{reviewer}/edit', [AdminReviewerController::class, 'edit'])->name('reviewers.edit');
        Route::put('reviewers/{reviewer}', [AdminReviewerController::class, 'update'])->name('reviewers.update');
        Route::delete('reviewers/{reviewer}', [AdminReviewerController::class, 'destroy'])->name('reviewers.destroy');

        Route::get('pimpinan', [AdminPimpinanController::class, 'index'])->name('pimpinan.index');
        Route::get('pimpinan/create', [AdminPimpinanController::class, 'create'])->name('pimpinan.create');
        Route::post('pimpinan', [AdminPimpinanController::class, 'store'])->name('pimpinan.store');
        Route::get('pimpinan/{pimpinan}/edit', [AdminPimpinanController::class, 'edit'])->name('pimpinan.edit');
        Route::put('pimpinan/{pimpinan}', [AdminPimpinanController::class, 'update'])->name('pimpinan.update');
        Route::delete('pimpinan/{pimpinan}', [AdminPimpinanController::class, 'destroy'])->name('pimpinan.destroy');

        Route::get('narasumber', [AdminNarasumberController::class, 'index'])->name('narasumber.index');
        Route::get('narasumber/create', [AdminNarasumberController::class, 'create'])->name('narasumber.create');
        Route::post('narasumber', [AdminNarasumberController::class, 'store'])->name('narasumber.store');
        Route::get('narasumber/{narasumber}/edit', [AdminNarasumberController::class, 'edit'])->name('narasumber.edit');
        Route::put('narasumber/{narasumber}', [AdminNarasumberController::class, 'update'])->name('narasumber.update');
        Route::delete('narasumber/{narasumber}', [AdminNarasumberController::class, 'destroy'])->name('narasumber.destroy');

        Route::get('landing-settings', [LandingSettingController::class, 'edit'])->name('landing-settings.edit');
        Route::put('landing-settings', [LandingSettingController::class, 'update'])->name('landing-settings.update');

        Route::get('schedule-sessions', [ScheduleSessionController::class, 'index'])->name('schedule-sessions.index');
        Route::post('schedule-sessions', [ScheduleSessionController::class, 'store'])->name('schedule-sessions.store');
        Route::put('schedule-sessions/{scheduleSession}', [ScheduleSessionController::class, 'update'])->name('schedule-sessions.update');
        Route::delete('schedule-sessions/{scheduleSession}', [ScheduleSessionController::class, 'destroy'])->name('schedule-sessions.destroy');

        Route::post('schedule-sessions/{scheduleSession}/slots', [PresentationSlotController::class, 'store'])->name('slots.store');
        Route::delete('slots/{presentationSlot}', [PresentationSlotController::class, 'destroy'])->name('slots.destroy');

        Route::get('certificates', [AdminCertificateController::class, 'index'])->name('certificates.index');
        Route::post('certificates', [AdminCertificateController::class, 'store'])->name('certificates.store');
        Route::get('certificates/preview', [AdminCertificateController::class, 'preview'])->name('certificates.preview');

        Route::post('certificate-templates', [CertificateTemplateController::class, 'store'])->name('certificate-templates.store');
        Route::delete('certificate-templates/{certificateTemplate}', [CertificateTemplateController::class, 'destroy'])->name('certificate-templates.destroy');

        Route::get('announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
        Route::delete('announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');

        Route::get('reports', [ReportController::class, 'index'])->name('reports');
    });

    // Reviewer
    Route::middleware('role:reviewer')->prefix('reviewer')->name('reviewer.')->group(function () {
        Route::get('articles', [ReviewController::class, 'index'])->name('articles.index');
        Route::get('articles/{assignment}', [ReviewController::class, 'show'])->name('articles.show');
        Route::post('articles/{assignment}/review', [ReviewController::class, 'store'])->name('articles.review');
    });

    // Moderator
    Route::middleware('role:moderator')->prefix('moderator')->name('moderator.')->group(function () {
        Route::get('sessions', [ModeratorSessionController::class, 'index'])->name('sessions.index');
        Route::put('slots/{slot}/attendance', [ModeratorSessionController::class, 'recordAttendance'])->name('slots.attendance');
        Route::post('slots/{slot}/assessment', [ModeratorSessionController::class, 'storeAssessment'])->name('slots.assessment');
    });

    // Pimpinan
    Route::middleware('role:pimpinan')->prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('dashboard', [StatisticsController::class, 'index'])->name('dashboard');
    });

    // Narasumber
    Route::middleware('role:narasumber')->prefix('narasumber')->name('narasumber.')->group(function () {
        Route::get('dashboard', [NarasumberDashboardController::class, 'index'])->name('dashboard');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
