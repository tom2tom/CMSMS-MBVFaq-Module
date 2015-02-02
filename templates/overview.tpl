{* template for frontend display of faqs
do not rename classes faqcatlink, faqcatblock, faqlink, faqanswer (js needs them) *}
{if $catcount > 0}
{if $catcount > 1}
<ol class="faqcatlist">
{/if}
	{foreach from=$cats item=category}
{if $catcount > 1}
	<li><span class="faqcatlink">{$category->category_link}</span>
	<div class="faqcatblock">
{/if}
	<ol class="faqlist">
	{foreach from=$category->items item=one}
		<li><span class="faqlink">{$one->itemlink}</span><div id="faq{$one->divid}" class="faqanswer">{$one->answer}</div></li>
	{/foreach}
	</ol>
{if $catcount > 1}
	</div>
	</li>
{/if}
	{/foreach}
{if $catcount > 1}
</ol>
{/if}
{else}
{$noitems}
{/if}
{$jquery}
