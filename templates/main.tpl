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
			<div id="brddesc">{{ header_desc|raw }}</div>
		</div>

		<div id="brdmenu" class="inbox">
			<ul>
{% for link in navigation_links %}
				{{ link|raw }}
{% endfor %}
			</ul>
		</div>

		<div id="brdwelcome" class="inbox">


{% if is_array(status_info) %}
			<ul class="conl">
	{% for info in status_info %}
			{{ info|raw }}
	{% endfor %}
			</ul>
{% else %}
			{{ status_info|raw }}
{% endif %}


{% if topic_searches is not empty %}
			<ul class="conr">
				<li><span>{{ lang.t('Topic searches') }} {{ topic_searches|join(' | ')|raw }}</span></li>
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
			<div class="usercontent">{{ config['o_announcement_message']|raw }}</div>
		</div>
	</div>
</div>
{% endif %}

{# Main page #}
<div id="brdmain">

{{ page_output|raw }}

</div>

{# Footer #}
<div id="brdfooter" class="block">
	<h2><span>{{ lang.t('Board footer') }}</span></h2>
	<div class="box">

{% if footer_style is defined and (footer_style == 'viewforum' or footer_style == 'viewtopic') or is_admmod %}
		<div id="modcontrols" class="inbox">

{% if footer_style == 'viewforum' %}
			<dl>
				<dt><strong>{{ lang.t('Mod controls') }}</strong></dt>
				<dd><span><a href="moderate.php?fid={{ forum_id }}&amp;p={{ p }}">{{ lang.t('Moderate forum') }}</a></span></dd>
			</dl>
{% elseif footer_style == 'viewtopic' %}
			<dl>
				<dt><strong>{{ lang.t('Mod controls') }}</strong></dt>
				<dd><span><a href="moderate.php?fid={{ forum_id }}&amp;tid={{ id }}&amp;p={{ p }}">{{ lang.t('Moderate topic') }}</a></span></dd>
				<dd><span><a href="moderate.php?fid={{ forum_id }}&amp;move_topics={{ id }}">{{ lang.t('Move topic') }}</a></span></dd>

{% if cur_topic['closed'] == 1 %}
				<dd><span><a href="moderate.php?fid={{ forum_id }}&amp;open={{ id }}">{{ lang.t('Open topic') }}</a></span></dd>
{% else %}
				<dd><span><a href="moderate.php?fid={{ forum_id }}&amp;close={{ id }}">{{ lang.t('Close topic') }}</a></span></dd>
{% endif %}

{% if cur_topic['sticky'] == 1 %}
				<dd><span><a href="moderate.php?fid={{ forum_id }}&amp;unstick={{ id }}">{{ lang.t('Unstick topic') }}</a></span></dd>
{% else %}
				<dd><span><a href="moderate.php?fid={{ forum_id }}&amp;stick={{ id }}">{{ lang.t('Stick topic') }}</a></span></dd>
{% endif %}

			</dl>
{% endif %}

			<div class="clearer"></div>
		</div>
{% endif %}

		<div id="brdfooternav" class="inbox">
			<div class="conl">
{% if quickjump is defined and quickjump is not empty %}
				<form id="qjump" method="get" action="viewforum.php">
					<div>
						<label>
							<span>{{ lang.t('Jump to') }}<br /></span>
							<select name="id" onchange="window.location=('viewforum.php?id='+this.options[this.selectedIndex].value)">
{% for cur_cat in quickjump %}								<optgroup label="{{ cur_cat.cat_name }}">
{% for cur_forum in cur_cat.forums %}
								<option value="{{ cur_forum.fid }}"{{ forum_id is defined and forum_id == cur_forum.fid ? ' selected="selected"' : ''}}>{{ cur_forum.forum_name }}{{ cur_forum.redirect_url == '' ? '' : ' &gt;&gt;&gt;' }}</option>
{% endfor %}								</optgroup>
{% endfor %}

							</select>
							<input type="submit" value="{{ lang.t('Go') }}" accesskey="g" />
						</label>
					</div>
				</form>
{% endif %}
			</div>
			<div class="conr">

{% if footer_style == 'index' %}
{% if config['o_feed_type'] == 1 %}				<p id="feedlinks"><span class="rss"><a href="extern.php?action=feed&amp;type=rss">{{ lang.t('RSS active topics feed') }}</a></span></p>
{% elseif config['o_feed_type'] == 2 %}				<p id="feedlinks"><span class="atom"><a href="extern.php?action=feed&amp;type=atom">{{ lang.t('Atom active topics feed') }}</a></span></p>
{% endif %}

{% elseif footer_style == 'viewforum' %}
{% if config['o_feed_type'] == 1 %}				<p id="feedlinks"><span class="rss"><a href="extern.php?action=feed&amp;fid={{ forum_id }}&amp;type=rss">{{ lang.t('RSS forum feed') }}</a></span></p>
{% elseif config['o_feed_type'] == 2 %}				<p id="feedlinks"><span class="atom"><a href="extern.php?action=feed&amp;fid={{ forum_id }}&amp;type=atom">{{ lang.t('Atom forum feed') }}</a></span></p>
{% endif %}

{% elseif footer_style == 'viewtopic' %}
{% if config['o_feed_type'] == 1 %}				<p id="feedlinks"><span class="rss"><a href="extern.php?action=feed&amp;tid={{ id }}&amp;type=rss">{{ lang.t('RSS topic feed') }}</a></span></p>
{% elseif config['o_feed_type'] == 2 %}				<p id="feedlinks"><span class="atom"><a href="extern.php?action=feed&amp;tid={{ id }}&amp;type=atom">{{ lang.t('Atom topic feed') }}</a></span></p>
{% endif %}

{% endif %}
				<p id="poweredby">{% set powered_by %}<a href="http://fluxbb.org/">FluxBB</a>{% if config['o_show_version'] == '1' %} {{ config['o_cur_version'] }}{% endif %}{% endset %}{{ lang.t('Powered by', powered_by)|raw }}</p>
			</div>
			<div class="clearer"></div>
		</div>
	</div>
</div>

{% if defined('PUN_DEBUG') %}<p id="debugtime">[{{ lang.t('Querytime', query_time, num_queries) }}{% if memory_usage is defined %} - {{ lang.t('Memory usage', memory_usage) }}{% endif %}{% if peak_usage is defined %} {{ lang.t('Peak usage', peak_usage) }}{% endif %}  ]</p>{% endif %}

</div>
<div class="end-box"><div><!-- Bottom corners --></div></div>
</div>

</body>
</html>
