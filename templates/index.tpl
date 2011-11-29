
{% for cat_idx,cur_category in categories %}

<div id="idx{{ cat_idx }}" class="blocktable">
<h2><span>{{ cur_category.cat_name }}</span></h2>
<div class="box">
	<div class="inbox">
		<table cellspacing="0">
		<thead>
			<tr>
				<th class="tcl" scope="col">{{ lang.t('Forum') }}</th>
				<th class="tc2" scope="col">{{ lang.t('Topics') }}</th>
				<th class="tc3" scope="col">{{ lang.t('Posts') }}</th>
				<th class="tcr" scope="col">{{ lang.t('Last post') }}</th>
			</tr>
		</thead>
		<tbody>

{% for key,cur_forum in cur_category['forums'] %}

			<tr class="{{ key is even ? 'roweven' : 'rowodd' }}{% if cur_forum.new_posts is defined %} inew{% endif %}{% if cur_forum.redirect_url is not empty %} iredirect{% endif %}">
				<td class="tcl">
					<div class="icon{% if cur_forum.new_posts is defined %} icon-new{% endif %}"><div class="nosize">{{ key }}</div></div>
					<div class="tclcon">
						<div>
							<h3>{% if cur_forum.redirect_url is not empty %}<span class="redirtext">{{ lang.t('Link to') }}</span> {% endif %}<a href="viewforum.php?id={{ cur_forum.fid }}">{{ cur_forum.forum_name }}</a>{% if cur_forum.new_posts %}<span class="newtext">[ <a href="search.php?action=show_new&amp;fid={{ cur_forum.fid }}">{{ lang.t('New posts') }}</a> ]</span>{% endif %}</h3>
{% if cur_forum.forum_desc is not empty %}							<div class="forumdesc">{{ cur_forum.forum_desc|raw }}</div>{% endif %}
{% if cur_forum.moderators is not empty %}							<p class="modlist">(<em>{{ lang.t('Moderated by') }}</em> {% for mod_username, mod_id in cur_forum.moderators %}{{ not_first is defined ? ', ' : '' }}{% set not_first=1 %}{% if user['g_view_users'] == '1' %}<a href="profile.php?id={{ mod_id }}">{{ mod_username }}</a>{% else %}{{ mod_username }}{% endif %}{% endfor %})</p>{% endif %}
						</div>
					</div>
				</td>
				<td class="tc2">{{ cur_forum.redirect_url is empty ? cur_forum.num_topics|number : '-' }}</td>
				<td class="tc3">{{ cur_forum.redirect_url is empty ? cur_forum.num_posts|number : '-' }}</td>
				<td class="tcr">{% if cur_forum.last_post is not empty %}<a href="viewtopic.php?pid={{ cur_forum.last_post_id}}#p{{ cur_forum.last_post_id }}">{{ cur_forum.last_post|time }}</a> <span class="byuser">{{ lang.t('by') }} {{ cur_forum.last_poster }}</span>{% elseif cur_forum.redirect_url is not empty %}- - -{% else %}{{ lang.t('Never') }}{% endif %}</td>
			</tr>
{% endfor %}

		</tbody>
		</table>
	</div>
</div>

</div>

{% endfor %}

{% if actions is not empty %}
<div class="linksb">
	<div class="inbox crumbsplus">
		<p class="subscribelink clearb">{{ actions|join(' - ')|raw }}</p>
	</div>
</div>
{% endif %}

<div id="brdstats" class="block">
	<h2><span>{{ lang.t('Board info') }}</span></h2>
	<div class="box">
		<div class="inbox">
			<dl class="conr">
				<dt><strong>{{ lang.t('Board stats') }}</strong></dt>
				<dd><span>{% set num %}<strong>{{ stats['total_users']|number }}</strong>{% endset %}{{ lang.t('No of users', num)|raw }}</span></dd>
				<dd><span>{% set num %}<strong>{{ stats['total_topics']|number }}</strong>{% endset %}{{ lang.t('No of topics', num)|raw }}</span></dd>
				<dd><span>{% set num %}<strong>{{ stats['total_posts']|number }}</strong>{% endset %}{{ lang.t('No of posts', num)|raw }}</span></dd>
			</dl>
			<dl class="conl">
				<dt><strong>{{ lang.t('User info') }}</strong></dt>
				<dd><span>{% set last_user %}{% if user['g_view_users'] == 1 %}<a href="profile.php?id={{ stats.last_user.id }}">{{ stats.last_user.username }}</a>{% else %}{{ stats.last_user.username }}{% endif %}{% endset %}{{ lang.t('Newest user', last_user)|raw }}</span></dd>
{% if stats.users_online %}
				<dd><span>{% set num %}<strong>{{ stats.users_online|length|number }}</strong>{% endset %}{{ lang.t('Users online', num)|raw }}</span></dd>
				<dd><span>{% set num %}<strong>{{ stats.num_guests_online|number }}</strong>{% endset %}{{ lang.t('Guests online', num)|raw }}</span></dd>
			</dl>
{% if stats.users_online is not empty %}
			<dl id="onlinelist" class="clearb">
				<dt><strong>{{ lang.t('Online') }} </strong></dt>
{% for user_id, ident in stats.users_online %}<dd>{% if user['g_view_users'] == 1 %}<a href="profile.php?id={{ user_id }}">{{ ident }}</a>{% else %}{{ ident }}{% endif %}</dd>{% endfor %}

{% endif %}
{% else %}
			</dl>
			<div class="clearer"></div>
{% endif %}

		</div>
	</div>
</div>