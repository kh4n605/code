
============== Fade Up Animation =================

<script>
document.addEventListener('DOMContentLoaded', function () {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) return;

  const targets = document.querySelectorAll('.animate');

  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (
        entry.isIntersecting &&
        entry.target.classList.contains('animate')
      ) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
  });

  targets.forEach(el => {
    observer.observe(el);
  });
});

</script>
<style>
@media (prefers-reduced-motion: no-preference) {
  .animate {
    opacity: 0;
    transform: translateY(100px);
    transition: opacity 1s ease, transform 0.6s ease;
    will-change: opacity, transform;
  }

  .animate.is-visible {
    opacity: 1;
    transform: translateY(0);
  }

  /* Disable animation in the editor preview */
  .editor-styles-wrapper .animate {
    opacity: 1 !important;
    transform: none !important;
    transition: none !important;
  }
}
</style>

function kh4n605_countdown_shortcode($atts) {
    $atts = shortcode_atts( array(
        'date' => '2025-10-15 00:00:00', // default
    ), $atts, 'countdown' );

    ob_start();
    ?>
    <div id="kh4n605-countdown">
      <div><span id="days">00</span><small>DAYS</small></div>
      <div><span id="hours">00</span><small>HRS</small></div>
      <div><span id="minutes">00</span><small>MINS</small></div>
      <div><span id="seconds">00</span><small>SECS</small></div>
    </div>

    <script>
    (function(){
      const targetDate = new Date("<?php echo esc_js($atts['date']); ?>").getTime();

      function updateCountdown() {
        const now = new Date().getTime();
        const distance = targetDate - now;

        if (distance < 0) {
          document.getElementById("kh4n605-countdown").innerHTML = "EXPIRED";
          return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("days").innerText = days;
        document.getElementById("hours").innerText = ("0" + hours).slice(-2);
        document.getElementById("minutes").innerText = ("0" + minutes).slice(-2);
        document.getElementById("seconds").innerText = ("0" + seconds).slice(-2);
      }

      updateCountdown();
      setInterval(updateCountdown, 1000);
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('kh4n605_countdown', 'kh4n605_countdown_shortcode');
