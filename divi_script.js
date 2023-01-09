
/* FLASHING CONTENT AT THE START */

var elm=document.getElementsByTagName("html")[0];elm.style.display="none";document.addEventListener("DOMContentLoaded",function(event) {elm.style.display="block"; });

/* FLASHING CONTENT AT THE END */

/** Remove Image Attribute Text on Hover START **/

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
/** Remove Image Attribute Text on Hover END **/

/* === DESIGN DIVI HAMBURGER START === */

const menuBtn = document.querySelector('.mobile_menu_bar');
	let menuOpen = false;
	menuBtn.addEventListener('click',()=>{
		if(!menuOpen){
			menuBtn.classList.add('opened');
			menuOpen = true;
		}else{
			menuBtn.classList.remove('opened');
			menuOpen = false;
		}
	});

/* === DESIGN DIVI HAMBURGER END === */