
{#
    "loading"       => $loader,
    "settings"      => [],
    "search"        => "
#}

<div class="container pt-3 pb-3 comp-settings-list sik-form-init">
    {{ loading|raw }}
    <div class="row">
        <div class="col">
            <input id="filter-settings-list" class="form-control form-control-sm input-carret" type="text" placeholder="Search" aria-label="Search settings list" />
        </div>
        {# <div class="col-5 {{ total_failed < 1 ? "d-none" }}">
            <div class="alert alert-danger d-flex align-items-center failed-label" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="">
                    Errors In <strong>{{ total_failed }}</strong> Settings - Check logs!
                </div>
            </div>
        </div> #}
    </div>
    <div class="row">
        {% for group_name, group in settings %}
            
                <div class="col-12 settings-row" data-group='{{ group_name }}'>
                    <ul class="settings-header">
                        <li class="group-name">
                            <span class="material-icons">settings_applications</span>
                            <strong>{{ group_name|capitalize }}</strong>
                        </li>
                        <li class="group-description">
                            <span>Settings: {{ group|length }}</span>
                        </li>
                        <li class="group-expand">
                            <i class="fas fa-chevron-down"></i>
                        </li>
                    </ul>
                    <div class="group-settings">
                        {% for setting_key, setting in group %}
                            <h4>
                                <span class="material-icons fs-3">tune</span>
                                <span class="setting-key fs-7">{{ setting_key }}</span>
                                <span class="setting-description fs-9 fw-light fst-italic">{{ setting.description }}</span>
                                <button class="btn btn-bsik-action btn-sm float-end icon-danger" data-setting="{{ setting_key|e('html_attr') }}" data-action="open-edit-settings-module">
                                    <i class='fas fa-pen'></i>&nbsp;&nbsp;EDIT
                                </button>
                            </h4>
                            {% set params_definition = setting|array_filter_keys('default','option','description') %}
                            {% set header_keys = "Current" %}
                            {% set header_values = setting.calculated %}
                            {% set empty_message = "Setting is not defined " %}
                            {% include 'params_table.tpl' %}
                        {% endfor %}
                    </div>
                </div>
        {% endfor %}
    </div>
</div>