
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