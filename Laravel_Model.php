<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
 
class Company extends Model
{
    use SoftDeletes, HasRoles;
    
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive'
    ];
 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'ra_prefix', 'status', 'exclude_from_search', 'stamp', 'logo', 'template_id', 'admin_id'
    ];
 
    /**
     * Validation rules
     *
     * @var array
     */
    public $rules = [
        'name' => 'required',
        'username' => 'required',
        'last_name' => 'required',
        'templates' => 'required',
        'email' => 'required|string|max:255|email|unique:users',
        'password' => 'required|min:7',
        'ra_prefix' => 'unique:companies|max:5'
    ];
 
    /**
     * Departments relation
     */
    public function departments()
    {
        return $this->hasMany(Department::class);
    }
 
    /**
     * Users relation
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
 
    /**
     * Active Users relation
     */
    public function active_users()
    {
        return $this->hasMany(User::class)->where('status', 'active');
    }
 
    /**
     * Template relation
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
 
    /**
     * Main Manager relation
     */
    public function main_manager()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
 
    /**
     * Templates relation
     */
    public function templates()
    {
        return $this->hasMany(CompanyTemplate::class);
    }
 
    /**
     * Get templates available for this company
     * @return array
     */
    public function getCompanyTemplates()
    {
        $templates = Template::all()
            ->pluck('title', 'id');
        $companyTemplates = CompanyTemplate::where('company_id', $this->id)
            ->pluck('template_id')->toArray();
 
        foreach ($templates as $num => $template) {
            if (!in_array($num, $companyTemplates)) {
                unset($templates[$num]);
            }
        }
        return $templates;
    }
 
    /**
     * Update templates available for this company
     * @param array $data
     * @return boolean
     */
    public function updateCompanyTemplates($data)
    {
        $this->templates()->delete();
 
        foreach ($data as $template) {
            $this->templates()->create(['template_id' => $template]);
        }
        return true;
    }
 
}