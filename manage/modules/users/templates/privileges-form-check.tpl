

<div class='container form-selected-priv' id="{{ form_id|e("html_attr") }}">
    {% for group, tags in privileges %}

        {# if meta is defined use it values: #}
        {% if attribute(groups_meta, group) is defined %}
            {% set icon = attribute(groups_meta, group).icon ?? 'fa-cog' %}
            {% set desc = attribute(groups_meta, group).description ?? '' %}
        {% else %}
            {% set icon = 'fa-cog' %}
            {% set desc = '' %}
        {% endif %}
        
        <div class="row mb-2">
            <span class="group-meta p-0">
                <i class="fas {{ icon|e("html_attr") }}"></i>
                <span class="group-meta-name">{{ group|upper }}</span>
                <span class="group-meta-description">{{ desc|capitalize }}</span>
            </span>
            <ul class="tags list-group list-group-horizontal" data-privgroup="{{ group|lower|e("html_attr") }}">
                {% for tag, state in tags %}
                    {% set id   = 'tag_' ~ group ~ '_' ~ tag %}
                    {% set name = group ~ '.' ~ tag %}
                    <li  class="list-group-item">
                        <div class="form-check form-switch">
                            <input 
                                id="{{ id|lower|e("html_attr") }}" 
                                name="{{ name|lower|e("html_attr") }}" {{ state ? "checked" }} 
                                data-current="{{ state ? "granted" }}" 
                                data-privtag="{{ tag|lower|e("html_attr") }}" 
                                class="form-check-input priv-checkbox" 
                                type="checkbox" 
                            />
                            <label class="form-check-label" for="{{ id|lower|e("html_attr") }}">
                                {{ tag|capitalize }}
                            </label>
                        </div>
                    </li>
                {% endfor %}
            </ul>
        </div>
    {% endfor %}
</div>