<div class="row g-3 role-metadata-form" id="{{ id|e("html_attr") }}">
  <div class="col-md-6">
    <label for="input-role-name" class="form-label">Role Name <em>(will be converted to lowercase)</em></label>
    <input type="text" class="form-control is-invalid" id="input-role-name" name="role_name">
    <div class="invalid-feedback">
        Role name is required, Use a-z and 0-9 characters minimum length of 3 is required. 
    </div>
  </div>
  <div class="col-md-6">
    <label for="input-role-color" class="form-label">Color</label>
    <div class="input-group">
        <input type="text"  class="form-control" id="input-role-color" name="role_color" placeholder="custom or select" aria-label="role color" />
        <input type="color" class="form-control form-control-color" value="#444444" title="Choose your color" />
    </div>
  </div>
  <div class="col-12">
    <div class="form-floating">
        <textarea class="form-control is-invalid" placeholder="Describe the role" id="input-role-desc" name="role_desc" style="height: 80px"></textarea>
        <label for="input-role-desc">Description</label>
        <div class="invalid-feedback">
        Maximum length of description is 250 characters long.
        </div>
    </div>
  </div>
</div>