{{ doc_head|raw }}
    <div class="main-page">
        <div class="container-bar noselect">
            {{ doc_admin_bar|raw }}
        </div>
        <div class="content-wrapper">
            <div class="container-side-menu noselect">
                {{ doc_side_menu|raw }}
            </div>
            <div class="container-module">
                {{ doc_module_header|raw }}
                <div id="module-content">
                    {{ module_content|raw }}
                </div>
            </div>
        </div>
        <div class="container-footer">
            <span class="plat-by">{{ brand }}</span>
            <div class="console-messages">
                <span class="user-select-none">
                    <i class="fas fa-comment-alt"></i>
                    &nbsp;&nbsp;Console Log
                    <em>0</em>
                    <i class="fas fa-angle-up open-icon"></i>
                </span>
                <ul>
                    {% if demo_notification %}
                        <li><span class='notify-info'>some messages words not wrappped lksjdflkj jsdf kjsdfkjj slkdfjlsdf lkjsdf<i class="fas fa-times"></i></span></li>
                        <li><span class='notify-warn'>some messages words not wrappped<i class="fas fa-times"></i></span></li>
                        <li><span class='notify-error'>some messages words not wrappped lksjdflkj jsdf kjsdfkjj slkdfjlsdf lkjsdf<i class="fas fa-times"></i></span></li>
                    {% endif %}
                </ul>
            </div>
        </div>
        <!-- START : extra added elements -->
            {{ extra_html|join|raw }}
        <!-- END : extra added elements -->
    </div>
{{ doc_end|raw }}