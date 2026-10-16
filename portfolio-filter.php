<?php
/*
Plugin Name: Portfolio Filter
Description: Project grid with AJAX category filter and likes.
Version: 1.0
*/

// stop direct access
if ( ! defined( 'ABSPATH' ) ) exit;

// Register custom post type and taxonomy
add_action( 'init', function () {

    register_post_type( 'project', [
        'label'     => 'Projects',
        'public'    => true,
        'supports'  => [ 'title', 'editor', 'thumbnail' ],
        'menu_icon' => 'dashicons-portfolio',
    ] );

    register_taxonomy( 'project_category', 'project', [
        'label'        => 'Project Categories',
        'hierarchical' => true,
    ] );

} );


// Render project cards based on category
function pf_render_cards( $cat = '' ) {
    $args = [
        'post_type'      => 'project',
        'posts_per_page' => 12,
    ];

    if ( $cat ) {
        $args['tax_query'] = [[
            'taxonomy' => 'project_category',
            'field'    => 'slug',
            'terms'    => $cat,
        ]];
    }

    $q = new WP_Query( $args );

    ob_start();
    if ( $q->have_posts() ) {
        while ( $q->have_posts() ) {
            $q->the_post();
            $likes = (int) get_post_meta( get_the_ID(), 'pf_likes', true );
            ?>
            <div class="pf-card">
                <h3><?php the_title(); ?></h3>
                <p><?php echo esc_html( wp_trim_words( get_the_content(), 20 ) ); ?></p>
                <button class="pf-like" data-id="<?php the_ID(); ?>">♥ <span><?php echo $likes; ?></span></button>
            </div>
            <?php
        }
    } else {
        echo '<p>No projects found.</p>';
    }
    wp_reset_postdata();
    return ob_get_clean();
}

// Shortcode to display the project grid with filters
add_shortcode( 'project_grid', function () {
    $terms = get_terms( [ 'taxonomy' => 'project_category' ] );

    $html  = '<div class="pf-filters">';
    $html .= '<button data-cat="">All</button>';
    foreach ( $terms as $t ) {
        $html .= '<button data-cat="' . esc_attr( $t->slug ) . '">' . esc_html( $t->name ) . '</button>';
    }
    $html .= '</div>';
    $html .= '<div id="pf-grid">' . pf_render_cards() . '</div>';

    return $html;
} );

//  css
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'pf-style', plugin_dir_url( __FILE__ ) . 'assets/style.css', [], '1.0' );
} );


// js
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'pf-js',
        plugin_dir_url( __FILE__ ) . 'assets/filter.js',
        [ 'jquery' ],
        '1.0',
        true
    );

    wp_localize_script( 'pf-js', 'pfData', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'pf_nonce' ),
    ] );
} );


// AJAX callback for filtering projects
add_action( 'wp_ajax_pf_filter', 'pf_filter_cb' );
add_action( 'wp_ajax_nopriv_pf_filter', 'pf_filter_cb' );

function pf_filter_cb() {
    // 1. Check nonce for security
    check_ajax_referer( 'pf_nonce', 'nonce' );

    // 2. Get the category from POST data, sanitize it
    $cat = isset( $_POST['cat'] ) ? sanitize_text_field( $_POST['cat'] ) : '';

    // 3. Render the project cards for the given category and return as JSON
    wp_send_json_success( [ 'html' => pf_render_cards( $cat ) ] );
}


// AJAX callback for liking a project

add_action( 'wp_ajax_pf_like', 'pf_like_cb' );
add_action( 'wp_ajax_nopriv_pf_like', 'pf_like_cb' );

function pf_like_cb() {
    // 1. Check nonce for security
    check_ajax_referer( 'pf_nonce', 'nonce' );

    // 2. Convert the received id to a positive integer
    $id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

    // 3. Verify that this id is indeed a project, otherwise return an error
    if ( ! $id || get_post_type( $id ) !== 'project' ) {
        wp_send_json_error( [ 'message' => 'Invalid project' ] );
    }

    // 4. Read the old value, increment it, and save it back to the database
    $n = (int) get_post_meta( $id, 'pf_likes', true ) + 1;
    update_post_meta( $id, 'pf_likes', $n );

    // 5. Return the new like count as JSON
    wp_send_json_success( [ 'likes' => $n ] );
}