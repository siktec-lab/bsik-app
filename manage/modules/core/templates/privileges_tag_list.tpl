




{% if privileges_tags is defined and privileges_tags is iterable and privileges_tags is not empty %}

    <ul class="endpoint-priv-tags">
        {% for group, privs in privileges_tags %} 
            <li class="priv-tags-group">
                <i class="fas fa-layer-group"></i>
                {{ group|upper }}
                <i class="fas fa-angle-double-right"></i>
                {{ privs|join(', ')|upper }}
            </li>
        {% endfor %}
    </ul>

{% else %}

    <span class="no-policy">
        No permission policy is defined - Only parent module policy is enforced.
    </span>

{% endif %}
