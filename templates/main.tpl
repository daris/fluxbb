<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{{ lang_identifier }}" lang="{{ lang_identifier }}" dir="{{ content_direction }}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{ title }}</title>
<link rel="stylesheet" type="text/css" href="style/{{ user.style }}.css" />
{% if admin_style_file is defined %}<link rel="stylesheet" type="text/css" href="{{ admin_style_file }}" />{% endif %}
<!--[if lte IE 6]><script type="text/javascript" src="style/imports/minmax.js"></script><![endif]-->
{% if defined('PUN_ALLOW_INDEX') %}<meta name="ROBOTS" content="NOINDEX, FOLLOW" />{% endif %}
{% if required_fields is not empty %}
<script type="text/javascript">
/* <![CDATA[ */
function process_form(the_form)
{
	var element_names = new Array();

	{% for elem_orig,elem_trans in required_fields %}
	element_names["{{ elem_orig }}"] = "{{ elem_trans }}";
	{% endfor %}

	if (document.all || document.getElementById)
	{
		for (var i = 0; i < the_form.length; ++i)
		{
			var elem = the_form.elements[i];
			if (elem.name && (/^req_/.test(elem.name)))
			{
				if (!elem.value && elem.type && (/^(?:text(?:area)?|password|file)$/i.test(elem.type)))
				{
					alert('"' + element_names[elem.name] + '" {{ lang.t('required field') }}');
					elem.focus();
					return false;
				}
			}
		}
	}
	return true;
}
/* ]]> */
</script>
{% endif %}
</head>

<body{% if focus_element is defined %} onload="document.getElementById('{{ focus_element[0] }}').elements['{{ focus_element[1] }}'].focus();"{% endif %}>

<div id="pun{{ page }}" class="pun">
<div class="top-box"><div><!-- Top Corners --></div></div>
<div class="punwrap">

<div id="brdheader" class="block">
	<div class="box">
		<div id="brdtitle" class="inbox">
			<h1><a href="index.php">{{ header_title }}</a></h1>
			<div id="brddesc">{% autoescape false %}{{ header_desc }}{% endautoescape %}</div>
		</div>

		<div id="brdmenu" class="inbox">
			<ul>
{% autoescape false %}
{% for link in navigation_links %}
				{{ link }}
{% endfor %}
{% endautoescape %}
			</ul>
		</div>

		<div id="brdwelcome" class="inbox">

{% autoescape false %}
{% if is_array(status_info) %}
			<ul class="conl">
	{% for info in status_info %}
			{{ info }}
	{% endfor %}
			</ul>
{% else %}
			{{ status_info }}
{% endif %}
{% endautoescape %}


{% if topic_searches is not empty %}
			<ul class="conr">
				<li><span>{{ lang.t('Topic searches') }} {% autoescape false %}{{ topic_searches|join(' | ') }}{% endautoescape %}</span></li>
			</ul>
{% endif %}

			<div class="clearer"></div>
		</div>
	</div>
</div>

{# Announcement message #}
{% if user['g_read_board'] == 1 and config['o_announcement_message'] == 1 %}
<div id="announce" class="block">
	<div class="hd"><h2><span>{{ lang.t('Announcement') }}</span></h2></div>
	<div class="box">
		<div id="announce-block" class="inbox">
			<div class="usercontent">{% autoescape false %}{{ config['o_announcement_message'] }}{% endautoescape %}</div>
		</div>
	</div>
</div>
{% endif %}

<div id="brdmain">

{% autoescape false %}
{{ page_output }}
{% endautoescape %}

</div>

<pun_footer>

</div>
<div class="end-box"><div><!-- Bottom corners --></div></div>
</div>

</body>
</html>
