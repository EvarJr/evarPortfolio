<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * ProjectModel — app/Models/ProjectModel.php
 */
class ProjectModel extends Model
{
    protected $table         = 'portfolio_projects';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'title','description','category','icon',
        'tech','github_url','demo_url','is_featured','sort_order'
    ];

    public function getAllOrdered(): array
    {
        return $this->orderBy('sort_order','ASC')->orderBy('id','ASC')->findAll();
    }

    public function getFeatured(): array
    {
        return $this->where('is_featured',1)
                    ->orderBy('sort_order','ASC')
                    ->findAll();
    }

    public function nextSortOrder(): int
    {
        $r = $this->selectMax('sort_order','m')->first();
        return (int)($r['m'] ?? 0) + 1;
    }

    /** Decode tech JSON to array */
    public static function decodeTech(string $json): array
    {
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    /** Encode tech array to JSON */
    public static function encodeTech(array $items): string
    {
        return json_encode(array_values(array_filter(array_map('trim', $items))));
    }
}
