
<div class="container pt-3 pb-3 comp-modules-list sik-form-init">
    <div class="row">
        {{ title_ele|raw }}
        <div class="container module-list-filter">
            <div class="row g-0">
                <div class="col">
                    <input id="filter-installed-list" class="form-control form-control-sm input-carret" type="text" placeholder="Filter installed module list" aria-label="filter module list" />
                </div>
            </div>
        </div>
        {% for module in modules %}
            <div class="container module-list" data-id="{{ module.name|e('html_attr') }}">
                <div class="row">
                    <div class="col col-2">
                        <span class="module-name"><i class="fas fa-puzzle-piece"></i>{{ module.name }}</span>
                        <span class="tag-module-status module-status-{{ module.status_color|e('html_attr') }}">{{ module.status_tag }}</span>
                    </div>
                    <div class="col col-3">
                        <ul class="module-info">
                            <li><strong>Version</strong>{{ module.version }}</li>
                            <li><strong>Author</strong>{{ module.author }}</li>
                            <li><strong>Website</strong>{{ module.web }}</li>
                            <li>{{ module.tags|raw }}</li>
                        </ul>
                    </div>
                    <div class="col">
                        <h4 class="module-header">{{ module.title }}</h4>
                        <p class="module-description">{{ module.description }}</p>
                    </div>
                    <div class="col col-2">
                        <span class="module-meta-header">Installed:</span>
                        <span class="module-meta-content">{{ module.date_installed }}</span>
                        <span class="module-meta-header">Requires:</span>
                        <span class="module-meta-content">--------------</span>
                        <span class="module-meta-header">Privileges:</span>
                        <span class="module-meta-content">--------------</span>
                    </div>
                    <div class="col col-2 module-actions">
                        <button type="button" class="btn btn-sm btn-bsik-action icon-info" data-action="open-settings-module" data-module="{{ module.name|e('html_attr') }}" {{ module.status != 'active' ? 'disabled' }}>
                            <i class="fas fa-cog"></i> Settings
                        </button>
                        {% if not module.is_core %}
                            <button type'button' class='btn btn-sm btn-bsik-action icon-danger' data-action='uninstall-module'>
                                <i class="fas fa-trash-alt"></i> Uninstall
                            </button>
                            <button type='button' class='btn btn-sm btn-bsik-action icon-warning' data-action='status-module' data-current='{{ module.status_toggle|e('html_attr') }}'>
                                <i class="fas fa-traffic-light"></i> {{ module.status_button }}
                            </button>
                        {% endif %}
                        {% if module.updates %}
                            <button type='button' class='btn btn-sm btn-bsik-action icon-success' data-action='update-module'>
                                <i class="fas fa-download"></i> Update
                            </button>
                        {% endif %}
                    </div>
                </div>
            </div>
        {% endfor %}
    </div>
</div>