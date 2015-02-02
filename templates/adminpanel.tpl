{if isset($message) && $message != ''}{$message}<br />{/if}
{$tab_headers}
{$start_items_tab}
{$startform1}
{if $icount > 0}
<div style="overflow:auto;">
	<table id="items" class="{if $mod}table_drag {/if}pagetable" style="border-collapse:collapse">
		<thead><tr>
			<th>{$idtext}</th>
			<th>{$itemtext}</th>
			<th>{$grptext}</th>
			<th>{$postdatetext}</th>
			<th>{$changedatetext}</th>
			<th>{$answerertext}</th>
			<th class="pageicon">{$activetext}</th>
{if $mod}	<th class="updown">{$movetext}</th>
			<th class="pageicon">&nbsp;</th>{/if}
{if $del}	<th class="pageicon">&nbsp;</th>{/if}
			<th class="checkbox" style="width:20px;">{if $icount > 1}{$selectall_items}{/if}</th>
		</tr></thead>
		<tbody>
 {foreach from=$items item=entry} {cycle values='row1,row2' name='c1' assign='rowclass'}
		<tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
			<td class="id">{$entry->id}</td>
			<td>{$entry->item}</td>
			<td>{$entry->group}</td>
			<td>{$entry->create_date}</td>
			<td>{$entry->modify_date}</td>
			<td>{$entry->ownername}</td>
			<td>{$entry->active}</td>
{if $mod}	<td class="updown">{$entry->downlink}{$entry->uplink}</td>
			<td>{$entry->editlink}</td>{/if}
{if $del}	<td>{$entry->deletelink}</td>{/if}
			<td class="checkbox">{$entry->selected}</td>
		</tr>
 {/foreach}
		</tbody>
	</table>
{if $mod && $icount > 1}<p class="dndhelp">{$dndhelp}</p>{/if}
</div>
{else}
 <p class="pageinput" style="margin:20px;">{$noitems}</p>
{/if}
<div class="pageoptions">
{if $add}{$additemlink}{/if}
{if $icount > 0}
 <div style="margin:0;float:right;text-align:right">{$exportbtn1}{if $mod}{if $icount > 1} {$sortbtn1}{/if} {$ablebtn1}{/if} {if $del}{$deletebtn1}{/if}</div>
 <div class="clearb"></div>
{/if}
</div>
{$endform}
{$end_tab}

{$start_grps_tab}
{$startform2}
{if $gcount > 0}
<div style="overflow:auto;">
	<table id="groups" class="{if $mod}table_drag {/if}pagetable" style="border-collapse:collapse">
		<thead><tr>
			<th>{$grpidtext}</th>
			<th>{$grptext}</th>
{if $grpown}<th>{$ownertext}</th>{/if}
{if $mod}	<th class="updown">{$movetext}</th>{/if}
{if $del}	<th class="pageicon">&nbsp;</th>{/if}
			<th class="checkbox" style="width:20px;">{if $gcount > 1}{$selectall_grps}{/if}</th>
		</tr></thead>
		<tbody>
 {foreach from=$grpitems item=entry} {cycle values='row1,row2' name='c2' assign='rowclass'}
		<tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
			<td class="id">{$entry->id}</td>
			<td>{$entry->hidden}{$entry->input_name}</td>
{if $grpown}<td>{$entry->input_owner}</td>{/if}
{if $mod}	<td class="updown">{$entry->downlink}{$entry->uplink}</td>{/if}
{if $del}	<td>{$entry->deletelink}</td>{/if}
			<td class="checkbox">{$entry->selected}</td>
		</tr>
 {/foreach}
		</tbody>
	</table>
{if $mod && $gcount > 1}<p class="dndhelp">{$dndhelp}</p>{/if}
</div>
{else}
	<p class="pageinput" style="margin:20px;">{$nogroups}</p>
{/if}
<div class="pageoptions">
{if $add}{$addgrplink}{/if}
{if $gcount > 0}
<div style="margin:0;float:right;text-align:right">
{$cancel}{$exportbtn2}{if $mod && $gcount > 1}{$sortbtn2}{/if}{if $del && $gcount > 1}{$deletebtn2}{/if}{if $mod}{$submitbtn2}{/if}
</div>
<div class="clearb"></div>
{/if}
</div>
{$endform}
{$end_tab}

{$start_settings_tab}
{if $adm}
{$startform3}
<div style="margin:20px;overflow:auto;">
{foreach from=$settings item=entry name=opts}
{$entry->input}  {$entry->title}<br />{if !$smarty.foreach.opts.last}<br />{/if}
{/foreach}
</div>
<div style="margin:0;float:right;text-align:right">
{$submitbtn3}{$cancel}
</div>
<div class="clearb"></div>
{$endform}
{else}
<p class="pageinput" style="margin:20px;">{$nopermission}</p>
{/if}
{$end_tab}

{$tab_footers}
