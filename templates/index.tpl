
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
{% if cur_forum.forum_desc is not empty %}							<div class="forumdesc">{% autoescape false %}{{ cur_forum.forum_desc }}{% endautoescape %}</div>{% endif %}
							{% if cur_forum.moderators is not empty %}<p class="modlist">(<em>{{ lang.t('Moderated by') }}</em>{% for cur_mod_id,cur_mod_username in cur_forum.moderators %}{{ user_link(cur_mod_id, cur_mod_username) }}{% endfor %}</p>{% endif %}
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