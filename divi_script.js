
/* FLASHING CONTENT AT THE START */
<script type="text/javascript">
var elm=document.getElementsByTagName("html")[0];elm.style.display="none";document.addEventListener("DOMContentLoaded",function(event) {elm.style.display="block"; });
</script>

/** Remove Image Attribute Text on Hove **/
<script>
jQuery(document).ready(function($) {
    $("img").mouseenter(function() {
        let $lwp_title = $(this).attr("title");
        $(this).attr("lwp_title", $lwp_title);
        $(this).attr("title", "");
    }).mouseleave(function() {
        let $lwp_title = $(this).attr("lwp_title");
        $(this).attr("title", $lwp_title);
        $(this).removeAttr("lwp_title");
    });
});
</script>