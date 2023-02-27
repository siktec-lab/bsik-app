{# The Bsik general editting settings object template #}
<form id='dyn-form-settings' class='form-settings p-1' {{ render_as_attributes(attrs)|raw }}>
    {% if values is iterable and options is defined and descriptions is defined and overridden is defined %}
        {% for key, value in values %}
            <div class="input-group mb-3">
                <label for="id-{{ key|e('html_attr') }}" class="form-label">
                    <strong><i class="fas fa-cog"></i>&nbsp;{{ key }}</strong>
                    {{ key in overridden ? "<span class='set-override'>overridden</span>"|raw }}
                    {{ key in inherited ? "<span class='set-inherited'>inherited</span>"|raw }}
                    <p title="{{ descriptions[key]|e('html_attr') }}" >{{ descriptions[key] ?? "" }}</p>
                </label>
                {% if options[key] is iterable %}
                    <select class="form-select" id="id-{{ key|e('html_attr') }}" name="{{ key|e('html_attr') }}" data-original="{{ value|e('html_attr') }}">
                        {% for opt in options[key] %}
                            <option value="{{ opt|e('html_attr') }}" {{ opt == value ? "selected='selected'" }}>{{ opt }}</option>
                        {% endfor %}
                    </select>
                {% elseif options[key] == "integer" or options[key] == "false" %}
                    <input 
                        type="number" 
                        step="{{ options[key] == "integer" ? '1' : '0.1' }}" 
                        class="form-control" 
                        id="id-{{ key|e('html_attr') }}" 
                        name="{{ key|e('html_attr') }}" 
                        data-original="{{ value|e('html_attr') }}" 
                        placeholder="{{ value|e('html_attr') }}" 
                    />
                    <div class="input-group-text">
                        <input class="form-check-input mt-0 checkbox-empty-disable-input" type="checkbox" value="" aria-label="Checkbox setting empty value" />
                        <span>Empty</span> 
                    </div>
                {% elseif options[key] == "boolean" %}
                    <select class="form-select" id="id-{{ key|e('html_attr') }}" name="{{ key|e('html_attr') }}" data-original="{{ value == true ? 'true' : 'false' }}">
                        <option value="true" {{ value == true   ? "selected='selected'" }}>TRUE</option>
                        <option value="false" {{ value == false ? "selected='selected'" }}>FALSE</option>
                    </select>
                {% else %}
                    <input type="text" class="form-control" id="id-{{ key|e('html_attr') }}" name="{{ key|e('html_attr') }}" data-original="{{ value|e('html_attr') }}" placeholder="{{ value|e('html_attr') }}" />
                    <div class="input-group-text">
                        <input class="form-check-input mt-0 checkbox-empty-disable-input" type="checkbox" value="" aria-label="Checkbox setting empty value" />
                        <span>Empty</span> 
                    </div>
                {% endif %}
                {% if key in overridden %}
                    <div class="input-group-text">
                        <input class="form-check-input mt-0 checkbox-remove-override-input" type="checkbox" value="" aria-label="Checkbox remove override value" />
                        <span>Remove</span> 
                    </div>
                {% endif %}
            </div>
        {% endfor %}
    {% else %}  
        <p>settings form not available.</p>
    {% endif %}
</form>