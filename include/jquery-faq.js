<script type="text/javascript">
//<![CDATA[
jQuery.fn.scrollMinimal = function(smooth) {
 var cTop = this.offset().top;
 var cHeight = this.outerHeight(true);
 var windowTop = $(window).scrollTop();
 var visibleHeight = $(window).height();

 if (cTop < windowTop) {
  if (smooth) {
    $('body').animate({'scrollTop': cTop}, 'slow', 'swing');
  } else {
    $(window).scrollTop(cTop);
  }
 } else if (cTop + cHeight > windowTop + visibleHeight) {
  if (smooth) {
    $('body').animate({'scrollTop': cTop - visibleHeight + cHeight}, 'slow', 'swing');
  } else {
    $(window).scrollTop(cTop - visibleHeight + cHeight);
  }
 }
};

$(function() {
 $('.faqlink a').click(function() {
  var blk=$(this).parent().next();
  var dis=(blk.css('display')=='none')?'block':'none';
  blk.css('display',dis);
  return false;
  }).parent().next().css('display','none');
 var hash = window.location.hash;
 if(hash) {
  var see = '#faq'+hash.slice(1).replace('.','-');
  $(see).css('display','block');
  $(see).scrollMinimal();
 }
 $('.faqcatlink a').click(function() {
  var blk=$(this).parent().next();
  var dis=(blk.css('display')=='none')?'block':'none';
  blk.css('display',dis);
  return false;
 });
});





//]]>
</script>
