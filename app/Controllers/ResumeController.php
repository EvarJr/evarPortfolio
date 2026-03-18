<?php

namespace App\Controllers;

use App\Models\HeaderModel;
use App\Models\SummaryModel;
use App\Models\HistoryModel;
use App\Models\PersonalSkillModel;
use App\Models\TechStackModel;
use App\Models\LanguageModel;
use App\Models\EducationModel;
use App\Models\CertificationModel;

/**
 * ResumeController — app/Controllers/ResumeController.php
 * Plain standalone resume page at /resume/plain
 */
class ResumeController extends BaseController
{
    public function index(): string
    {
        return view('resume/index', [
            'header'         => (new HeaderModel())->getHeader(),
            'summary'        => (new SummaryModel())->getSummary(),
            'history'        => (new HistoryModel())->getAllWithBullets(),
            'skills'         => (new PersonalSkillModel())->getAllOrdered(),
            'tech'           => (new TechStackModel())->getAllOrdered(),
            'languages'      => (new LanguageModel())->getAllOrdered(),
            'education'      => (new EducationModel())->getAllWithBullets(),
            'certifications' => (new CertificationModel())->getAllOrdered(),
            'isLoggedIn'     => $this->isLoggedIn(),
        ]);
    }
}
