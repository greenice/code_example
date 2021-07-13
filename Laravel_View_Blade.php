<div class="form-group{{ $errors->has('name') ? ' has-error' : ''}}">
    {!! Form::label('name', 'Company Name: ', ['class' => 'control-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
</div>
 
@if ($formMode !== 'edit')
<div class="form-group{{ $errors->has('username') ? ' has-error' : ''}}">
    {!! Form::label('username', 'Manager First Name: ', ['class' => 'control-label']) !!}
    {!! Form::text('username', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('username', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('last_name') ? ' has-error' : ''}}">
    {!! Form::label('last_name', 'Manager Last Name: ', ['class' => 'control-label']) !!}
    {!! Form::text('last_name', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('last_name', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('email') ? ' has-error' : ''}}">
    {!! Form::label('email', 'Manager Email: ', ['class' => 'control-label']) !!}
    {!! Form::email('email', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'off']) !!}
    {!! $errors->first('email', '<p class="help-block">:message</p>') !!}
</div>
@else
    <div class="form-group{{ $errors->has('admin_id') ? ' has-error' : ''}}">
        {!! Form::label('admin_id', 'Company Email: ', ['class' => 'control-label']) !!}
        {!! Form::select('admin_id', $managers ?? [], $company->admin_id ?? '',
            ['class' => 'form-control', 'multiple' => false]) !!}
    </div>
@endif
<div class="form-group{{ $errors->has('ra_prefix') ? ' has-error' : ''}}">
    {!! Form::label('ra_prefix', 'RA Prefix: ', ['class' => 'control-label']) !!}
    {!! Form::text('ra_prefix', null, ['class' => 'form-control', 'maxLength' => 5]) !!}
    {!! $errors->first('ra_prefix', '<p class="help-block">:message</p>') !!}
</div>
 
<div class="form-group{{ $errors->has('template_id') ? ' has-error' : ''}}">
    {!! Form::label('template_id', 'Default Template: ', ['class' => 'control-label']) !!}
    {!! Form::select('template_id', isset($companyTemplates['temp']) ? $companyTemplates['temp'] : [],
        (isset($company) && $company->template_id) ? $company->template_id : '',
        ['class' => 'form-control', 'multiple' => false]) !!}
</div>
 
@if ($isAdmin)
    <div class="form-group{{ $errors->has('templates[]') ? ' has-error' : ''}}">
        {!! Form::label('templates[]', 'Available Templates: ', ['class' => 'control-label']) !!}
        {!! Form::select('templates[]', $templates, (isset($companyTemplates) && $companyTemplates) ? $companyTemplates : '',
            ['class' => 'form-control templates', 'multiple' => true, 'required' => 'required']) !!}
    </div>
@endif
 
@if ($formMode !== 'edit')
    @php
        $passwordRequired = '';
        if ($formMode === 'create') {
            $passwordRequired = 'required';
        }
    @endphp
    <div class="form-group{{ $errors->has('password') ? ' has-error' : ''}}">
        {!! Form::label('password', 'Password: ', ['class' => 'control-label']) !!}
        <div class="input-group">
            <input type="text" class="form-control {{ $passwordRequired }} form-password" autocomplete="off"
                   minlength="7" name="password" aria-describedby="basic-addon1">
            <div class="input-group-append">
                <button class="btn btn-primary btn-border form-password-button"
                        type="button">Generate Password</button>
            </div>
        </div>
        {!! $errors->first('password', '<p class="help-block">:message</p>') !!}
    </div>
@endif
 
@if ($isAdmin)
<div class="form-group{{ $errors->has('status') ? ' has-error' : ''}}">
    {!! Form::label('status', 'Status: ', ['class' => 'control-label']) !!}
    {!! Form::select('status', $statuses, (isset($company) && $company->status) ? $company->status : '',
        ['class' => 'form-control', 'multiple' => false]) !!}
</div>
 
<div class="form-check{{ $errors->has('exclude_from_search') ? ' has-error' : ''}}">
    <div class="form-check">
        <label class="form-check-label">
            <input class="form-check-input" type="checkbox" name="exclude_from_search" value="1" @if (isset($company) && $company->exclude_from_search) checked @endif>
            <span class="form-check-sign">Exclude Materials of this Company from Search</span>
        </label>
    </div>
</div>
@endif
 
<div class="form-group">
    <div class="input-file input-file-image">
        <label for="stamp" class="control-label">Company Stamp (recommended size 150x150 pixels): </label>
        <div class="mb-4">
            <img src="{{ (isset($company) && $company->stamp) ? '/uploads/stamps/' . $company->stamp : '' }}" id="stamp-pic">
        </div>
        <input type="file" class="form-control form-control-file" id="file-upload-stamp" name="stamp-picture" accept="image/*">
        <label for="file-upload-stamp" class="btn btn-primary btn-round btn-lg"><i class="fa fa-file-image"></i> Upload stamp</label>
 
        <input type="hidden" name="stamp" id="stamp" value="{{ (isset($company) && $company->stamp) ? $company->stamp : '' }}">
    </div>
</div>
 
<div class="modal" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crop Image And Upload</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="resizer"></div>
                <button class="btn btn-block btn-dark" id="upload-pic" data-image-type="" >
                    Crop And Upload</button>
            </div>
        </div>
    </div>
</div>
 
<div class="form-group">
    <div class="input-file input-file-image">
        <label for="stamp" class="control-label">Company Logo (recommended size 150x150 pixels): </label>
        <div class="mb-4">
            <img src="{{ (isset($company) && $company->logo) ? '/uploads/logos/' . $company->logo : '' }}" id="logo-pic">
        </div>
        <input type="file" class="form-control form-control-file" id="file-upload-logo" name="logo-picture" accept="image/*">
        <label for="file-upload-logo" class="btn btn-primary btn-round btn-lg"><i class="fa fa-file-image"></i> Upload logo</label>
 
        <input type="hidden" name="logo" id="logo" value="{{ (isset($company) && $company->logo) ? $company->logo : '' }}">
    </div>
</div>
 
<div class="form-group">
    {!! Form::submit($formMode === 'edit' ? 'Update' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>