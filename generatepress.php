
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

// ===== Ajax Product Tabs ====

// Shortcode: [Kh4n605_cat_tabs tabs="memory-foam-mattress:Mattress,beds:Beds,bunk-beds:Bunk Beds"]
add_shortcode('Kh4n605_cat_tabs', function($atts) {

    // Default tabs
    $tabs = [
        'memory-foam-mattress' => 'Mattress',
        'beds'                 => 'Beds',
        'bunk-beds'            => 'Bunk Beds',
    ];

    // Override from shortcode attribute
    if ( isset($atts['tabs']) && trim($atts['tabs']) !== '' ) {
        $tabs = [];
        $pairs = explode(',', $atts['tabs']);
        foreach ($pairs as $pair) {
            $parts = explode(':', $pair);
            if(count($parts) === 2){
                $tabs[trim($parts[0])] = trim($parts[1]);
            }
        }
    }

    ob_start(); ?>
    
    <div class="kh4n605-tabs-wrapper">
        <div class="kh4n605-tab-buttons">
            <?php $i=0; foreach($tabs as $slug => $label): ?>
                <button class="kh4n605-tab-btn <?php echo $i===0?'active':''; ?>" data-category="<?php echo esc_attr($slug); ?>">
                    <?php echo esc_html($label); ?>
                </button>
            <?php $i++; endforeach; ?>
        </div>
        <div id="kh4n605-tab-content" class="kh4n605-products-grid"></div>
    </div>
    <?php
    // JS for AJAX
    add_action('wp_footer', function() {
    ?>
    <script>
    (function(){
        function loadProducts(cat){
            const data = new URLSearchParams();
            data.append('action','kh4n605_load_tab_products');
            data.append('category',cat);

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: data
            })
            .then(r => r.text())
            .then(html => {
                document.getElementById('kh4n605-tab-content').innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('kh4n605-tab-content').innerHTML = '<p>Error loading products.</p>';
            });
        }

        document.addEventListener('DOMContentLoaded', function(){
            const buttons = document.querySelectorAll('.kh4n605-tab-btn');
            if(!buttons.length) return;

            loadProducts(buttons[0].dataset.category); // load first tab

            buttons.forEach(btn => {
                btn.addEventListener('click', function(){
                    buttons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    loadProducts(this.dataset.category);
                });
            });
        });
    })();
    </script>
    <?php
    });

    return ob_get_clean();
});

// AJAX handler
add_action('wp_ajax_kh4n605_load_tab_products','kh4n605_load_tab_products');
add_action('wp_ajax_nopriv_kh4n605_load_tab_products','kh4n605_load_tab_products');

function kh4n605_load_tab_products(){
    if(empty($_POST['category'])){
        echo '<p>No category provided.</p>';
        wp_die();
    }

    $slug = sanitize_text_field($_POST['category']);
    $term = get_term_by('slug',$slug,'product_cat');
    if(!$term){
        echo '<p>Category slug not found: '.esc_html($slug).'</p>';
        wp_die();
    }

    $args = [
        'post_type'=>'product',
        'posts_per_page'=>8,
        'tax_query'=>[
            [
                'taxonomy'=>'product_cat',
                'field'=>'slug',
                'terms'=>[$slug],
                'include_children'=>true
            ]
        ],
        'post_status'=>'publish'
    ];

    $q = new WP_Query($args);

    if(!$q->have_posts()){
        echo '<p>No products found in "'.esc_html($term->name).'" or its subcategories.</p>';
        wp_die();
    }

    echo '<ul class="products">';
    while($q->have_posts()){
        $q->the_post();
        global $product;
        ?>
       <li class="product">
    <?php woocommerce_show_product_loop_sale_flash(); ?>
    <a href="<?php the_permalink(); ?>">
        <?php echo woocommerce_get_product_thumbnail(); ?>
        <h2 class="woocommerce-loop-product__title"><?php the_title(); ?></h2>
        <span class="price"><?php echo $product->get_price_html(); ?></span>
    </a>
</li>

        <?php
    }
    echo '</ul>';

    wp_reset_postdata();
    wp_die();
}

