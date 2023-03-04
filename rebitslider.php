<?php
/*
Plugin Name: Rebitslider
Plugin URI: https://yourpluginurl.com/
Description: A shortcode based carousel plugin for WordPress
Version: 1.0
Author: Your Name
Author URI: https://yourname.com/
*/

// Enqueue CSS and JS
function rebitslider_enqueue_scripts() {
    wp_enqueue_style( 'rebitslider-style', plugin_dir_url( __FILE__ ) . 'css/rebitslider.css' );
    wp_enqueue_script( 'rebitslider-script', plugin_dir_url( __FILE__ ) . 'js/rebitslider.js', array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'rebitslider_enqueue_scripts' );

// Define the shortcode
function rebitslider_shortcode( $atts ) {
    extract( shortcode_atts( array(
        'category' => '',
        'count' => 5,
        'order' => 'DESC',
    ), $atts ) );

    $options = get_option( 'rebitslider_settings' );
    $image_size = isset( $options['image_size'] ) ? esc_attr( $options['image_size'] ) : 'medium';

    $args = array(
        'category_name' => $category,
        'posts_per_page' => $count,
        'orderby' => 'date',
        'order' => $order,
    );
    $query = new WP_Query( $args );
    $output = '<div class="rebitslider">';
    while ( $query->have_posts() ) {
        $query->the_post();
        $image_id = get_post_thumbnail_id();
        $image_url = wp_get_attachment_image_src( $image_id, $image_size );
        $output .= '<div class="rebitslider-slide" style="background-image: url(' . $image_url[0] . ');">';
        $output .= '<h2>' . get_the_title() . '</h2>';
        $output .= '<div class="rebitslider-slide-content">' . get_the_content() . '</div>';
        $output .= '</div>';
    }
    $output .= '</div>';
    wp_reset_postdata();
    return $output;
}

// Add a new submenu item under "Posts"
function rebitslider_add_submenu_page() {
    add_submenu_page(
        'options-general.php',
        'Rebitslider Settings',
        'Rebitslider',
        'manage_options',
        'rebitslider-settings',
        'rebitslider_settings_callback'
    );
}
add_action( 'admin_menu', 'rebitslider_add_submenu_page' );

// Define the content of the new submenu page
function rebitslider_settings_callback() {
    ?>
    <div class="wrap">
        <h1>Rebitslider Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'rebitslider_settings_group' );
            do_settings_sections( 'rebitslider_settings_group' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Add settings fields to the new submenu page
function rebitslider_settings_init() {
    register_setting( 'rebitslider_settings_group', 'rebitslider_settings', 'rebitslider_settings_sanitize' );

    add_settings_section(
        'rebitslider_settings_section',
        'Slider Settings',
        'rebitslider_settings_section_callback',
        'rebitslider_settings_group'
    );
    
    add_settings_field(
        'rebitslider_image_size',
        'Image Size',
        'rebitslider_image_size_callback',
        'rebitslider_settings_group',
        'rebitslider_settings_section'
    );
    
    add_settings_field(
        'rebitslider_autoplay',
        'Autoplay',
        'rebitslider_autoplay_callback',
        'rebitslider_settings_group',
        'rebitslider_settings_section'
    );
    
    add_settings_field(
        'rebitslider_speed',
        'Speed',
        'rebitslider_speed_callback',
        'rebitslider_settings_group',
        'rebitslider_settings_section'
    );
}

// Define the content of the settings fields
function rebitslider_settings_section_callback() {
echo 'Customize the settings for your Rebitslider plugin:';
}

function rebitslider_image_size_callback() {
$options = get_option( 'rebitslider_settings' );
$image_size = isset( $options['image_size'] ) ? esc_attr( $options['image_size'] ) : '';
?>
<input type="text" name="rebitslider_settings[image_size]" value="<?php echo $image_size; ?>" class="regular-text">
<?php
}

function rebitslider_autoplay_callback() {
$options = get_option( 'rebitslider_settings' );
$autoplay = isset( $options['autoplay'] ) ? (int) $options['autoplay'] : 0;
?>
<input type="checkbox" name="rebitslider_settings[autoplay]" value="1" <?php checked( $autoplay, 1 ); ?>>
<?php
}

function rebitslider_speed_callback() {
$options = get_option( 'rebitslider_settings' );
$speed = isset( $options['speed'] ) ? (int) $options['speed'] : '';
?>
<input type="text" name="rebitslider_settings[speed]" value="<?php echo $speed; ?>" class="regular-text">
<?php
}

// Sanitize the settings input
function rebitslider_settings_sanitize( $input ) {
$output = array();
if ( isset( $input['image_size'] ) ) {
$output['image_size'] = sanitize_text_field( $input['image_size'] );
}
if ( isset( $input['autoplay'] ) ) {
$output['autoplay'] = 1;
} else {
$output['autoplay'] = 0;
}
if ( isset( $input['speed'] ) ) {
$output['speed'] = (int) $input['speed'];
}
return $output;
}

add_shortcode( 'rebitslider', 'rebitslider_shortcode' );    
