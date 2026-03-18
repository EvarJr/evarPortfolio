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
use App\Models\ProjectModel;
use App\Models\ResumeCollectionModel;
use App\Models\ThesisIsoModel;
use App\Models\ThesisPhaseModel;
use App\Models\SummaryModel;
use App\Models\TechStackModel;

class AboutController extends BaseController
{
    private function resumeData(): array
    {
        $rid = (new ResumeCollectionModel())->getActiveId();
        return [
            'header'         => (new HeaderModel())->getHeader($rid),
            'summary'        => (new SummaryModel())->getSummary($rid),
            'history'        => (new HistoryModel())->getAllWithBullets($rid),
            'skills'         => (new PersonalSkillModel())->getAllOrdered($rid),
            'tech'           => (new TechStackModel())->getAllOrdered($rid),
            'languages'      => (new LanguageModel())->getAllOrdered($rid),
            'education'      => (new EducationModel())->getAllWithBullets($rid),
            'certifications' => (new CertificationModel())->getAllOrdered($rid),
        ];
    }

    private function aboutData(): array
    {
        $projects = (new ProjectModel())->getFeatured();

        // Find thesis project and load its content
        $thesisProject = null;
        foreach ($projects as $p) {
            if ($p['category'] === 'thesis') { $thesisProject = $p; break; }
        }
        $thesisPhases = $thesisProject
            ? (new ThesisPhaseModel())->getForProject((int)$thesisProject['id'])
            : [];
        $isoScores = $thesisProject
            ? (new ThesisIsoModel())->getForProject((int)$thesisProject['id'])
            : [];

        return [
            'about'         => (new AboutModel())->getAbout(),
            'services'      => (new AboutServiceModel())->getAllOrdered(),
            'testimonials'  => (new AboutTestimonialModel())->getAllOrdered(),
            'projects'      => $projects,
            'thesisPhases'  => $thesisPhases,
            'isoScores'     => $isoScores,
            'isLoggedIn'    => $this->isLoggedIn(),
        ];
    }

    public function index(): string
    {
        return view('about/index', array_merge($this->aboutData(), $this->resumeData()));
    }

    public function resume(): string
    {
        return view('about/resume_print', array_merge($this->aboutData(), $this->resumeData()));
    }
}
