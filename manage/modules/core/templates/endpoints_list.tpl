
{# 
"module"        => $found->module,
"name"          => $endpoint_name,
"disk_location" => $found->working_dir,
"protected"     => !$found->protected,
"exposure"      => [
    "external"  => $found->external,
    "global"    => $found->global,
    "front"     => $found->front
],
"allowed"       => $found->policy->has_privileges($Api::$issuer_privileges),
"policy"        => $found->policy->all_granted(false),
"params"        => [
    "expected"  => $found->params,
    "rules"     => $found->conditions
] #}

<div class="container pt-3 pb-3 comp-endpoints-list sik-form-init">
    {{ loading|raw }}
    <div class="row">
        <div class="col">
            <input id="filter-endpoints-list" class="form-control form-control-sm input-carret" type="text" placeholder="Search" aria-label="Search api endpoints list" />
        </div>
        <div class="col-5 {{ total_failed < 1 ? "d-none" }}">
            <div class="alert alert-danger d-flex align-items-center failed-label" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="">
                    Errors In <strong>{{ total_failed }}</strong> Endpoints - Check logs!
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        {% for module in endpoints %}
            {% for endpoint in module %}
                <div class="col-12 endpoint-row" data-endpoint='{{ endpoint.name }}' data-module='{{ endpoint.module }}'>
                    <ul class="endpoint-header">
                        <li class="endpoint-name">
                            <i class="fas fa-puzzle-piece"></i>
                            <strong>{{ endpoint.module }}</strong>
                            <i class="fas fa-code-branch"></i>
                            <span>{{ endpoint.name }}</span>
                        </li>
                        <li class="endpoint-description">
                            <span>{{ endpoint.describe ?: "Not Described" }}</span>
                        </li>
                        <li class="endpoint-global">
                            Global: {{ endpoint.exposure.global ? "<i class='fas fa-check text-success'></i>" : "<i class='fas fa-times text-danger'></i>" }}
                        </li>
                        <li class="endpoint-external">
                            External: {{ endpoint.exposure.external ? "<i class='fas fa-check text-success'></i>" : "<i class='fas fa-times text-danger'></i>" }}
                        </li>
                        <li class="endpoint-front">
                            Front: {{ endpoint.exposure.front ? "<i class='fas fa-check text-success'></i>" : "<i class='fas fa-times text-danger'></i>" }}
                        </li>
                        <li class="endpoint-allowed">
                            Allowed: {{ endpoint.allowed ? "<i class='fas fa-user-shield text-success'></i>" : "<i class='fas fa-user-shield text-danger'></i>" }}
                        </li>
                        <li class="endpoint-expand">
                            <i class="fas fa-chevron-down"></i>
                        </li>
                    </ul>
                    <div class="endpoint-more-info">
                        <h4>
                            <i class="fas fa-calculator"></i>
                            Expected Parameters:
                        </h4>
                        {% set params_definition = endpoint.params.expected %}
                        {% set header_keys = "Name" %}
                        {% set header_values = "Default" %}
                        {% set empty_message = "Endpoint expect no parameters" %}
                        {% include 'params_table.tpl' %}
                        <h4>
                            <i class="fas fa-clipboard-check"></i>
                            Validation Rules:
                        </h4>
                        {% set params_definition = endpoint.params.rules %}
                        {% set header_keys = "Name" %}
                        {% set header_values = "Validation" %}
                        {% set empty_message = "No validation rules" %}
                        {% include 'params_table.tpl' %}
                        <h4>
                            <i class="fas fa-shield-alt"></i>
                            Required Permission Policy:
                        </h4>
                        {% set privileges_tags = endpoint.policy %}
                        {% include 'privileges_tag_list.tpl' %}
                    </div>
                </div>
            {% endfor %}
        {% endfor %}
    </div>
</div>