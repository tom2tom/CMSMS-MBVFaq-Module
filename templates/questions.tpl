{* For populating tbody of questions-table, via ajax.
Must be consistent with relevant part of adminpanel.tpl, except aspects managed by js *}
{foreach from=$items item=entry} {cycle values='row1,row2' name='c1' assign='rowclass'}
 <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
{if $dev} <td>{$entry->item_id}</td>{/if}
  <td>{$entry->hidden}{$entry->item}</td>
  <td>{$entry->group}</td>
  <td>{$entry->create_date}</td>
  <td>{$entry->modify_date}</td>
{if $own} <td>{$entry->ownername}</td>{/if}
  <td>{$entry->active}</td>
  <td>{$entry->editlink}</td>
{if $del}<td>{$entry->deletelink}</td>{/if}
  <td class="checkbox">{$entry->selected}</td>
 </tr>
{/foreach}
