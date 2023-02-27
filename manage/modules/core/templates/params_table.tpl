

{% if params_definition is defined and params_definition is iterable and params_definition is not empty %}
    <table class="parameters-table">
        <tr>
            <td class="header-style">
                {{ header_keys }}
            </td>
            {% for param in params_definition|keys %}
                <td>{{ param }}</td>
            {% endfor %}
        </tr>
        <tr>
            <td class="header-style">
                {{ header_values|print_variable }}
            </td>
            {% for value in params_definition %}
                    <td>{{ value|print_variable }}</td>
            {% endfor %}
        </tr>
    </table>
{% else %}
    <span class="no-params">
        {{ empty_message }}
    </span>
{% endif %}