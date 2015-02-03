<script type="text/javascript">
//<![CDATA[
$(function() {
 $('.table_drag').tableDnD({
	onDragClass: 'row1hover',
	onDrop: function(table, droprows) {
		var name;
		var odd = true;
		var oddclass = 'row1';
		var evenclass = 'row2';
		var droprow = $(droprows)[0];
		$(table).find('tbody tr').each(function() {
			name = odd ? oddclass : evenclass;
			if (this === droprow) {
				name = name+'hover';
			}
			$(this).removeClass().addClass(name);
			odd = !odd;
		});

		var allrows = $(droprow.parentNode).children();
		var curr = droprow.rowIndex - 2;
		var droporder = (curr < 0) ? 'null' : $(allrows[curr]).find('> td > span.id').html();
		curr++;
		var dropcount = droprows.length;
		while (dropcount > 0) {
			droporder = droporder+','+$(allrows[curr]).find('> td > span.id').html();
			curr++;
			dropcount--;
		}
		droporder = droporder+','+$(allrows[curr]).find('> td > span.id').html(); //'target' may be 'null'

		var ajaxdata = (table.id=='items') ?
			'|QDATA|'+droporder:
			'|CDATA|'+droporder;
		$.ajax({
		  type: 'POST',
		  url: 'moduleinterface.php',
		  data: ajaxdata,
		  success: dropresponse,
		  dataType: 'html'
		});
	}
 }).find('tbody tr').removeAttr('onmouseover').removeAttr('onmouseout')
 	 .mouseover(function() {
		var now = $(this).attr('class');
		$(this).attr('class', now+'hover');
 }).mouseout(function() {
		var now = $(this).attr('class');
		var to = now.indexOf('hover');
		$(this).attr('class', now.substring(0,to));
	});
 $('.updown').hide();
 $('.dndhelp').css('display','block');
});

function dropresponse(data,status)
{
 if (status=='success') {
  if (data != '') {
    $('#items > tbody').html(data);
  }
 } else {
  $('#page_tabs').prepend('<p style="font-weight:bold;color:red;">Server Communication Error!</p><br />');
 }
}

function select_all_items(b)
{
 var st = $(b).attr('checked');
 if(! st) st = false;
 $('input[name="|ID|selitems[]"][type="checkbox"]').attr('checked', st);
}

function selitm_count()
{
 var cb = $('input[name="|ID|selitems[]"]:checked');
 return cb.length;
}

function confirm_selitm_count()
{
 return (selitm_count() > 0);
}

function confirm_delete_item()
{
 if (selitm_count() > 0)
  return confirm('|CONF1|');
 return false;
}

function select_all_groups(b)
{
 var st = $(b).attr('checked');
 if(! st) st = false;
 $('input[name="|ID|selgrps[]"][type="checkbox"]').attr('checked', st);
}

function selgrp_count()
{
 var cb = $('input[name="|ID|selgrps[]"]:checked');
 return cb.length;
}

function confirm_selgrp_count()
{
 return (selgrp_count() > 0);
}

function confirm_delete_grp()
{
 if (selgrp_count() > 0)
  return confirm('|CONF2|');
 return false;
}
//]]>
</script>

