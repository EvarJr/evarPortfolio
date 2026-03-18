<?php

namespace App\Controllers;

use App\Models\AboutModel;
use App\Models\AboutServiceModel;
use App\Models\AboutTestimonialModel;
use App\Models\CertificationModel;
use App\Models\EducationModel;
use App\Models\HeaderModel;
use App\Models\HistoryModel;
use App\Models\LanguageModel;
use App\Models\PersonalSkillModel;
use App\Models\ResumeCollectionModel;
use App\Models\SummaryModel;
use App\Models\TechStackModel;

/**
 * AdminController — app/Controllers/AdminController.php
 *
 * Supports ?rid=N to edit a specific resume.
 * Defaults to the active resume when no ?rid given.
 */
class AdminController extends BaseController
{
    public function index(): string
    {
        // Which resume are we editing? Default = active
        $rcModel = new ResumeCollectionModel();
        $rid     = (int) ($this->request->getGet('rid') ?? 0);
        if (!$rid) $rid = $rcModel->getActiveId();

        return view('admin/dashboard', [
            // Resume collection
            'resumes'        => $rcModel->getAll(),
            'editingResumeId'=> $rid,

            // Resume content — filtered by $rid
            'header'         => (new HeaderModel())->getHeader($rid),
            'summary'        => (new SummaryModel())->getSummary($rid),
            'history'        => (new HistoryModel())->getAllWithBullets($rid),
            'skills'         => (new PersonalSkillModel())->getAllOrdered($rid),
            'tech'           => (new TechStackModel())->getAllOrdered($rid),
            'languages'      => (new LanguageModel())->getAllOrdered($rid),
            'education'      => (new EducationModel())->getAllWithBullets($rid),
            'certifications' => (new CertificationModel())->getAllOrdered($rid),

            // About Me (shared across all resumes)
            'about'          => (new AboutModel())->getAbout(),
            'services'       => (new AboutServiceModel())->getAllOrdered(),
            'testimonials'   => (new AboutTestimonialModel())->getAllOrdered(),

            'adminUsername'  => session()->get('admin_username') ?? 'admin',
        ]);
    }
}
