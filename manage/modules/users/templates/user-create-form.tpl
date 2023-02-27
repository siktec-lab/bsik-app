{% set icon_required = "<i class='fas fa-asterisk' style='color:#ff9900;font-size:0.5em;vertical-align:super'></i>&nbsp;" %}
{% set icon_invalid = "<i class='fas fa-exclamation-circle'></i>&nbsp;" %}
<div class="container">
    <form id="create-user-form" autocomplete="off">
        <div class="row mt-2 g-2">
            <div class="col">
                <div class="form-floating">
                    <input type="text" class="form-control" id="create-input-first-name" max-type="50" placeholder="User name" autocomplete="new-first-name" />
                    <label for="create-input-first-name">
                        {{ icon_required|raw }}<i class="fas fa-id-badge fw-normal"></i>&nbsp;First name:
                    </label>
                    <div class="invalid-feedback">
                        {{ icon_invalid|raw }} Please type the user first name - Minimum 2 chars long.
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="form-floating">
                    <input type="text" class="form-control" id="create-input-last-name" max-type="50" placeholder="last name" autocomplete="new-last-name" />
                    <label for="create-input-last-name">
                        {{ icon_required|raw }}<i class="fas fa-id-badge fw-normal"></i>&nbsp;Last name:
                    </label>
                    <div class="invalid-feedback">
                        {{ icon_invalid|raw }} Please type the user last name - Minimum 2 chars long.
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="form-floating">
                    <input type="text" class="form-control" id="create-input-company-name" max-type="50" placeholder="company name" autocomplete="new-company-name" />
                    <label for="create-input-company-name">
                        <i class="fas fa-building fw-normal"></i>&nbsp;Company:
                    </label>
                </div>
            </div>
        </div>
        <div class="row mt-2 g-2"> 
            <div class="col">
                <div class="form-floating">
                    <input type="email" class="form-control" id="create-input-email" max-type="250" placeholder="example@gamil.com" autocomplete="new-password" />
                    <label for="create-input-email">
                        {{ icon_required|raw }}<i class="fas fa-at"></i>&nbsp;Email address:
                    </label>
                    <div class="invalid-feedback">
                        {{ icon_invalid|raw }} Please type a valid and unique email address.
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-2 g-2"> 
            <div class="col">
                <div class="form-floating">
                    <input type="text" class="form-control" id="create-input-password" placeholder="set password" autocomplete="new-password" />
                    <label for="create-input-password">
                        {{ icon_required|raw }}<i class="fas fa-key"></i>&nbsp;Password (8+ chars):
                    </label>
                    <div class="invalid-feedback">
                        {{ icon_invalid|raw }} Please type a valid password - Min 8 chars use characters digits and symbols.
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="form-floating">
                    <input type="text" class="form-control" id="create-input-confirm-password" placeholder="confirm password" autocomplete="new-password" />
                    <label for="create-input-confirm-password">
                        {{ icon_required|raw }}<i class="fas fa-key"></i>&nbsp;Confirm password:
                    </label>
                    <div class="invalid-feedback">
                        {{ icon_invalid|raw }} Passwords does not match.
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-2 g-2">
            <div class="col">
                <div class="form-floating">
                    <select class="form-select" id="create-input-role">
                        {% for role in roles %}
                            <option value='{{ role.id|raw }}'>{{ role.role|upper }} - {{ role.description }}</option>
                        {% endfor %}
                    </select>
                    <label for="create-input-role">
                        {{ icon_required|raw }}<i class="fas fa-user-shield"></i>&nbsp;User role group:
                    </label>
                </div>
            </div>
            <div class="col">
                <div class="form-floating">
                    <select class="form-select" id="create-input-status">
                        <option value="0">Active</option>
                        <option value="2">Suspended</option>
                    </select>
                    <label for="create-input-role">
                        {{ icon_required|raw }}<i class="fas fa-unlock-alt"></i>&nbsp;Account state:
                    </label>
                </div>
            </div>
        </div>
        <div class="row mt-2 g-2">
            <div class="col">
                <div class="form-floating">
                    <textarea class="form-control" placeholder="About text" id="create-input-about" max-type="250" style="height: 100px"></textarea>
                    <label for="create-input-about">
                        <i class="fas fa-comment-alt fw-normal"></i>&nbsp;About:
                    </label>
                </div>
            </div>
        </div>
    </form>
</div>