{$backtomod_nav}<br />
{$startform}
<div class="pageoverflow">
	<p class="pagetext">*{$category_text}:</p>
	<p class="pageinput">{$input_category}</p>
	<p class="pagetext">{$order_number_text}:</p>
	<p class="pageinput">{$input_order_number}<br />{$help_order_number}</p>
	<p class="pagetext">{$short_question_text}:</p>
	<p class="pageinput lolines">{$input_short_question}</p>
	<p class="pagetext">{$long_question_text}:</p>
	<p class="pageinput midlines">{$input_long_question}</p>
	<p class="pagetext">{$short_answer_text}:</p>
	<div class="pageinput"><span class="midlines">{$input_short_answer}</span><br />{$help_use_smarty}</div>
	<p class="pagetext">{$long_answer_text}:</p>
	<div class="pageinput"><span class="hilines">{$input_long_answer}</span><br />{$help_use_smarty}</div>
	<p class="pagetext">{$active_text}:</p>
	<p class="pageinput">{$input_active}</p>
{if isset($create_date_text)}	<p class="pagetext">{$create_date_text}:</p>
	<p class="pageinput">{$create_date}</p>{/if}
	<br />
{if $mod > 0}{$hidden}{/if}
	<p class="pageinput">{if $mod > 0}{$submit}{/if}{$cancel}</p>
</div>
{$endform}
