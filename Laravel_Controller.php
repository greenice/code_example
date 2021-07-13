<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Models\Hazard;
use App\Models\Template;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use App\Events\UserCreated;
 
class CompaniesController extends Controller
{
    const PER_PAGE = 15;
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $keyword = $request->get('search');
        
 
        if (!empty($keyword)) {
            $companies = Company::where('name', 'LIKE', "%$keyword%")
                ->latest();
        }
        else {
            $companies = Company::latest();
        }
 
        if (!$authUser->hasRole(User::ROLE_ADMIN)){
            $companies = $companies->whereId($authUser->company_id);
        }
 
        $companies = $companies->paginate(self::PER_PAGE);
        $statuses = Company::STATUSES;
 
        return view('admin.companies.index', compact('companies', 'statuses'));
    }
 
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $authUser = Auth::user();
        if (!$authUser->hasRole(User::ROLE_ADMIN)) {
            return abort(401);
        }
 
        $templates = Template::whereRaw('company_id is null')->pluck('title', 'id');
        $statuses = Company::STATUSES;
 
        $companyTemplates = [];
        $companyTemplates['temp'] = [];
        return view('admin.companies.create', compact('statuses', 'templates', 'companyTemplates'));
    }
 
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser->hasRole(User::ROLE_ADMIN)) {
            return abort(401);
        }
        $company = new Company;
 
        $this->validate(
            $request, $company->rules
        );
        $data = $request->except('password');
        $data['password'] = bcrypt($request->password);
        if ($data['ra_prefix'] == '') {
            $data['ra_prefix'] = substr(str_replace(" ", "", $data['name']),0,3);
        }
        $company = $company->create($data);
 
        $data['name'] = $request->username;
        $data['signature'] = '';
        $user = $company->users()->create($data);
        if ($user) {
            Event::fire(new UserCreated($user, $request->password));
        }
        $user->assignRole(User::ROLE_MANAGER);
        $company->admin_id = $user->id;
        $company->save();
 
        if (isset($data['templates'])) {
            $company->updateCompanyTemplates($data['templates']);
        }
 
        return redirect('companies')->with('flash_message', 'Company added!');
    }
 
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $authUser = Auth::user();
        $company = Company::findOrFail($id);
        if (!($authUser->hasRole($authUser::ROLE_ADMIN)) && ($company->id != $authUser->company_id)) {
            return abort(401);
        }
 
        $user = User::where('id', $company->admin_id)->first();
 
        return view('admin.companies.show', compact('company', 'user'));
    }
 
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $authUser = Auth::user();
        $company = Company::findOrFail($id);
        if (!($authUser->hasRole($authUser::ROLE_ADMIN)) && ($company->id != $authUser->company_id)) {
            return abort(401);
        }
 
        $statuses = Company::STATUSES;
        $templates = Template::where('company_id', $company->id)->orWhereRaw('company_id is null')->pluck('title', 'id');
 
        $companyTemplates = [];
        foreach ($company->templates as $template) {
            $companyTemplates[] = $template->template_id;
            $companyTemplates['temp'][$template->template_id] = $templates[$template->template_id];
        }
 
        $managers = User::getCompanyManagers($company->id)->pluck('email', 'id');
 
        return view('admin.companies.edit', compact('company', 'statuses', 'templates', 'companyTemplates', 'managers'));
    }
 
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $authUser = Auth::user();
        $company = Company::findOrFail($id);
        if (!($authUser->hasRole($authUser::ROLE_ADMIN)) && ($company->id != $authUser->company_id)) {
            return abort(401);
        }
 
        $this->validate(
            $request, [
                'name' => 'required',
                'templates' => $authUser->hasRole($authUser::ROLE_ADMIN) ? 'required' : '',
                'ra_prefix' => 'required|max:5|unique:companies,ra_prefix,' . $id
            ]
        );
        $data = $request->except('password', 'exclude_from_search');
        if ($request->has('password')) {
            $data['password'] = bcrypt($request->password);
        }
        if ($authUser->hasRole($authUser::ROLE_ADMIN)) {
            $data['exclude_from_search'] = $request->get('exclude_from_search') ? 1 : 0;
        }
 
        $company->update($data);
        if (isset($data['templates'])) {
            $company->updateCompanyTemplates($data['templates']);
        }
 
        return redirect('companies')->with('flash_message', 'Company updated!');
    }
 
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $authUser = Auth::user();
        if (!$authUser->hasRole(User::ROLE_ADMIN)) {
            return abort(401);
        }
        Company::destroy($id);
 
        return redirect('companies')->with('flash_message', 'Company deleted!');
    }
}