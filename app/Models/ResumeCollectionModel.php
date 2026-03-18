<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ResumeCollectionModel
 * app/Models/ResumeCollectionModel.php
 *
 * Manages the `resumes` master table.
 * Each row represents one complete independent resume.
 */
class ResumeCollectionModel extends Model
{
    protected $table         = 'resumes';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['name', 'is_active'];

    /** Get all resumes ordered by id */
    public function getAll(): array
    {
        return $this->orderBy('id', 'ASC')->findAll();
    }

    /** Get the currently active resume */
    public function getActive(): ?array
    {
        return $this->where('is_active', 1)->first();
    }

    /** Get active resume ID (defaults to 1 if none set) */
    public function getActiveId(): int
    {
        $active = $this->getActive();
        return $active ? (int) $active['id'] : 1;
    }

    /** Set one resume as active, deactivate all others */
    public function setActive(int $id): void
    {
        // Deactivate ALL resumes using a safe where clause
        $this->db->table('resumes')->set('is_active', 0)->where('id >', 0)->update();

        // Activate the chosen one
        $this->update($id, ['is_active' => 1]);
    }

    /** Create a blank new resume */
    public function createBlank(string $name): int
    {
        return (int) $this->insert(['name' => $name, 'is_active' => 0]);
    }

    /** Clone an existing resume — copies all content tables */
    public function cloneResume(int $sourceId, string $newName): int
    {
        $db    = \Config\Database::connect();
        $newId = $this->createBlank($newName);

        // Tables to clone (no bullets — they follow their parent)
        $tables = [
            'resume_header',
            'resume_summary',
            'resume_history',
            'resume_personal_skills',
            'resume_tech_stack',
            'resume_languages',
            'resume_education',
            'resume_certifications',
        ];

        foreach ($tables as $table) {
            $rows = $db->table($table)
                       ->where('resume_id', $sourceId)
                       ->get()->getResultArray();

            foreach ($rows as $row) {
                $oldId        = $row['id'];
                $row['resume_id'] = $newId;
                unset($row['id']);
                $insertedId   = $db->table($table)->insert($row)
                                   ? $db->insertID()
                                   : null;

                // Clone bullets for history and education
                if ($insertedId) {
                    if ($table === 'resume_history') {
                        $this->cloneBullets(
                            $db, 'resume_history_bullets',
                            'history_id', $oldId, $insertedId, $newId
                        );
                    }
                    if ($table === 'resume_education') {
                        $this->cloneBullets(
                            $db, 'resume_education_bullets',
                            'education_id', $oldId, $insertedId, $newId
                        );
                    }
                }
            }
        }

        return $newId;
    }

    private function cloneBullets(
        $db, string $table, string $fk,
        int $oldParentId, int $newParentId, int $newResumeId
    ): void {
        $bullets = $db->table($table)
                      ->where($fk, $oldParentId)
                      ->get()->getResultArray();

        foreach ($bullets as $b) {
            unset($b['id']);
            $b[$fk]         = $newParentId;
            $b['resume_id'] = $newResumeId;
            $db->table($table)->insert($b);
        }
    }

    /** Delete a resume and ALL its content */
    public function deleteResume(int $id): bool
        {
            // Cannot delete the only remaining resume
            if ($this->countAllResults() <= 1) return false;

            $wasActive = (bool) ($this->find($id)['is_active'] ?? false);

            $db = \Config\Database::connect();
            $tables = [
                'resume_history_bullets',
                'resume_education_bullets',
                'resume_header',
                'resume_summary',
                'resume_history',
                'resume_personal_skills',
                'resume_tech_stack',
                'resume_languages',
                'resume_education',
                'resume_certifications',
            ];

            foreach ($tables as $table) {
                $db->table($table)->where('resume_id', $id)->delete();
            }

            $this->delete($id);

            // If we deleted the active one, activate the first remaining
            if ($wasActive) {
                $first = $this->orderBy('id', 'ASC')->first();
                if ($first) $this->setActive($first['id']);
            }

            return true;
        }
}
