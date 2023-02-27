
{# current #}
{# current_sub_menu #}
{# entry ["text", "title", "icon", "action", "sub"] #}
{% include('debug.tpl') %}
<ul class='admin-menu ps-left'>

    {% for entry in menu %}

        <li class="menu-entry {{ entry.has_sub ? 'has-submenu' }} {{ entry.loaded ? 'is-loaded' }} {{ entry.loaded and entry.has_sub ? 'open-menu' }}" 
            data-menuact="{{ entry.action|e('html_attr') }}" 
            title="{{ entry.title|e('html_attr') }}">
            <a href="{{ entry.has_sub ? 'javascript:void(0)' : entry.url|e('html_attr') }}">
                <span>
                    {% if entry.icon starts with 'fas' or entry.icon starts with 'fab' %}
                        <i class="{{ entry.icon|e('html_attr') }}"></i>{{ entry.text|e }}
                    {% else %}
                        <span class="material-icons-outlined xlg space-4">{{ entry.icon }}</span>{{ entry.text|e }}
                    {% endif %}
                </span>
            </a>
            {% if entry.has_sub %}
                
                <ul class="entry-sub-menu">
                    {% for sentry in entry.sub_menu %}
                        <li class="menu-entry {{ sentry.loaded ? 'is-loaded' }}" 
                            data-menuact="{{ sentry.action|e('html_attr') }}" 
                            title="{{ sentry.title|e('html_attr') }}">
                            <a href="{{ sentry.url|e('html_attr') }}">
                                <span>
                                    {% if sentry.icon starts with 'fas' or sentry.icon starts with 'fab' %}
                                        <i class="{{ sentry.icon|e('html_attr') }}"></i>{{ sentry.text|e }}
                                    {% else %}
                                        <span class="material-icons-outlined lg space-4">{{ sentry.icon }}</span>{{ sentry.text|e }}
                                    {% endif %}
                                </span>
                            </a>
                        </li>
                    {% endfor %}
                </ul>
            {% endif %}
        </li>

    {% endfor %}

</ul>