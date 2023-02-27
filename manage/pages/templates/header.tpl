<!doctype {{ doc_type }}>
<html> 
    <head>
        <meta charset="{{ meta.charset }}" />
        <meta name="viewport"               content="{{ meta.viewport }}" />
        <meta name="author"                 content="{{ meta.author }}" />
        <meta name="description"            content="{{ meta.description }}" />
        <meta http-equiv="X-UA-Compatible"  content="IE=7" />
        <meta name="api"            content="{{ meta.api }}" />
        <meta name="module"         content="{{ meta.module }}" />
        <meta name="module-sub"     content="{{ meta.module_sub }}" />
        {{ meta_token|raw }}
        {{ include('favicon.tpl') }}
        {{ ex_meta|join|raw }}
        <title>{{ meta.title }}</title>
        <!-- START : Head includes -->
        {{ css_libs|raw }}
        {{ js_libs|raw }}
        <!-- END : Head includes -->
        
    </head>
    <body {{ body_tag|raw }} >
        <!-- START : Body includes -->
        {{ body_css_includes|raw }}
        <!-- END : Body includes -->