<?php
// DISPLAY NAVBAR
wp_nav_menu(array(
    'menu'         => 'primary',
    'menu_class'   => 'navbar-nav',
    'li_class'     => 'nav-item',
    'a_class'      => 'nav-link',
    'active_class' => 'active',
));

// SET NAVBAR li CLASS
function add_additional_class_on_li($classes, $item, $args)
{
    if (isset($args->li_class)) {
        $classes[] = $args->li_class;
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'add_additional_class_on_li', 1, 3);

// SET NAVBAR a CLASS
function add_menu_link_class($atts, $item, $args)
{
    if (property_exists($args, 'a_class')) {
        $atts['class'] = $args->a_class;
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_menu_link_class', 1, 3);

// REGISTER WIDGETS
function ibrahim_khan_widget_init()
{
    register_sidebar(array(
        'name'          => __('Footer 1', 'ibrahim_khan'),
        'id'            => 'khan_ft_1',
        'description'   => __('Widgets in this area will be shown on Footer 1.', 'textdomain'),
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget'  => '</li>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'ibrahim_khan_widget_init');

// DISPLAY WIDGETS
dynamic_sidebar('khan_ft_1');

// SEE ALL METHODS OF A CLASS 
print_r(get_class_methods(WC()->cart)); // WC()->cart is a class

// Remove WP admin menu page/link
add_action('admin_init', function () {
    remove_menu_page('edit.php?post_type=participants-database');
    remove_menu_page('wpcf7');
});

// AUTO COMPLETE ADDRESS WITH GOOGLE PLACES 

function load_google_map_api(){ ?>  
    <script> 
        let autocomplete;
        let address1Field;
        let address2Field;
        let postalField;
        
        function initAutocomplete() {
          address1Field = document.querySelector("#input"); // INPUT FIELD ID
          address2Field = document.querySelector("#address2");
          postalField = document.querySelector("#postcode");
          // Create the autocomplete object, restricting the search predictions to
          // addresses in the US and Canada.
          autocomplete = new google.maps.places.Autocomplete(address1Field, {
            componentRestrictions: { country: ["us", "ca"] },
            fields: ["address_components", "geometry"],
            types: ["address"],
          });
          address1Field.focus();
          // When the user selects an address from the drop-down, populate the
          // address fields in the form.
          autocomplete.addListener("place_changed", fillInAddress);
        }
        
        function fillInAddress() {
          // Get the place details from the autocomplete object.
          const place = autocomplete.getPlace();
          let address1 = "";
          let postcode = "";
          for (const component of place.address_components) {
            // @ts-ignore remove once typings fixed
            const componentType = component.types[0];
        
            switch (componentType) {
              case "street_number": {
                address1 = `${component.long_name} ${address1}`;
                break;
              }
        
              case "route": {
                address1 += component.short_name;
                break;
              }
        
              case "postal_code": {
                postcode = `${component.long_name}${postcode}`;
                break;
              }
        
              case "postal_code_suffix": {
                postcode = `${postcode}-${component.long_name}`;
                break;
              }
              case "locality":
                document.querySelector("#locality").value = component.long_name;
                break;
              case "administrative_area_level_1": {
                document.querySelector("#state").value = component.short_name;
                break;
              }
              case "country":
                document.querySelector("#country").value = component.long_name;
                break;
            }
          }
        
          address1Field.value = address1;
          postalField.value = postcode;
          address2Field.focus();
        }
        
        window.initAutocomplete = initAutocomplete;
         </script>

    <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDGhGk3DTCkjF1EUxpMm5ypFoQ-ecrS2gY&callback=initAutocomplete&libraries=places&v=weekly"
    defer
  ></script>
<?php }
add_action('wp_head','load_google_map_api',10);