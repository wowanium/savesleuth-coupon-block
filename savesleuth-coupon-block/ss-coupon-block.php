<?php
/**
 * Plugin Name: SaveSleuth Coupon Block
 * Description: Gutenberg coupon block with a shared offers library in wp-admin, masked coupon codes, affiliate links, and multilingual labels.
 * Version: 1.4.1
 * Author: REKOWSKI
 * Text Domain: savesleuth-coupon-block
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SSCB_VERSION', '1.4.1' );
define( 'SSCB_PATH', plugin_dir_path( __FILE__ ) );
define( 'SSCB_URL', plugin_dir_url( __FILE__ ) );

function sscb_get_labels( $lang ) {
    $map = array(
        'de' => array(
            'active_code'  => 'Aktiver Code',
            'show_code'    => 'Code aufdecken',
            'code_copied'  => 'Code kopiert',
            'go_shop'      => 'Zum Shop',
            'read_review'  => 'Review lesen',
            'discount'     => 'Rabatt',
            'shop'         => 'Shop',
            'description'  => 'Angebote und Rabattbedingungen können sich jederzeit ändern.',
            'deal_tip'     => 'Unser aktueller Deal-Tipp',
            'reveal_strip' => 'Zum Aufdecken klicken',
        ),
        'fr' => array(
            'active_code'  => 'Code actif',
            'show_code'    => 'Révéler le code',
            'code_copied'  => 'Code copié',
            'go_shop'      => 'Voir la boutique',
            'read_review'  => 'Lire l’avis',
            'discount'     => 'Réduction',
            'shop'         => 'Boutique',
            'description'  => 'Les offres et conditions des codes promo peuvent changer à tout moment.',
            'deal_tip'     => 'Notre bon plan du moment',
            'reveal_strip' => 'Cliquez pour révéler',
        ),
        'en' => array(
            'active_code'  => 'Active code',
            'show_code'    => 'Reveal code',
            'code_copied'  => 'Code copied',
            'go_shop'      => 'Go to shop',
            'read_review'  => 'Read review',
            'discount'     => 'Discount',
            'shop'         => 'Store',
            'description'  => 'Offers and coupon terms can change at any time.',
            'deal_tip'     => 'Our current deal pick',
            'reveal_strip' => 'Click to reveal',
        ),
    );

    return isset( $map[ $lang ] ) ? $map[ $lang ] : $map['en'];
}

function sscb_mask_code( $code ) {
    $code   = trim( (string) $code );
    $length = function_exists( 'mb_strlen' ) ? mb_strlen( $code ) : strlen( $code );

    if ( $length <= 4 ) {
        return str_repeat( '•', max( 4, $length ) );
    }

    $start = min( 3, max( 1, (int) floor( $length / 3 ) ) );
    $end   = min( 2, max( 1, (int) floor( $length / 4 ) ) );

    if ( function_exists( 'mb_substr' ) ) {
        $prefix = mb_substr( $code, 0, $start );
        $suffix = mb_substr( $code, -$end );
    } else {
        $prefix = substr( $code, 0, $start );
        $suffix = substr( $code, -$end );
    }

    $dots = str_repeat( '•', max( 4, $length - $start - $end ) );
    return $prefix . $dots . $suffix;
}

function sscb_register_offer_cpt() {
    $labels = array(
        'name'               => 'SaveSleuth Coupons',
        'singular_name'      => 'Coupon Offer',
        'menu_name'          => 'SaveSleuth Coupons',
        'name_admin_bar'     => 'Coupon Offer',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Coupon Offer',
        'new_item'           => 'New Coupon Offer',
        'edit_item'          => 'Edit Coupon Offer',
        'view_item'          => 'View Coupon Offer',
        'all_items'          => 'All Coupon Offers',
        'search_items'       => 'Search Coupon Offers',
        'not_found'          => 'No coupon offers found.',
        'not_found_in_trash' => 'No coupon offers found in Trash.',
    );

    register_post_type(
        'ss_coupon_offer',
        array(
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-tickets-alt',
            'supports'            => array( 'title' ),
            'has_archive'         => false,
            'show_in_rest'        => false,
            'rewrite'             => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
        )
    );
}
add_action( 'init', 'sscb_register_offer_cpt' );

function sscb_offer_fields() {
    return array(
        'store_name'    => array( 'label' => 'Store name', 'type' => 'text', 'default' => '' ),
        'discount'      => array( 'label' => 'Discount', 'type' => 'text', 'default' => '10%' ),
        'coupon_code'   => array( 'label' => 'Coupon code', 'type' => 'text', 'default' => '' ),
        'affiliate_url' => array( 'label' => 'Affiliate URL', 'type' => 'url', 'default' => '' ),
        'review_url'    => array( 'label' => 'Review URL', 'type' => 'text', 'default' => '' ),
        'language'      => array( 'label' => 'Language', 'type' => 'select', 'default' => 'de', 'options' => array( 'de' => 'Deutsch', 'en' => 'English', 'fr' => 'Français' ) ),
        'variant'       => array( 'label' => 'Variant', 'type' => 'select', 'default' => 'full', 'options' => array( 'full' => 'Full', 'compact' => 'Compact' ) ),
        'headline'      => array( 'label' => 'Custom headline (optional)', 'type' => 'text', 'default' => '' ),
        'description'   => array( 'label' => 'Custom description (optional)', 'type' => 'textarea', 'default' => '' ),
        'copy_opens'    => array( 'label' => 'Open affiliate link after clicking “show code”', 'type' => 'checkbox', 'default' => '1' ),
        'show_review'   => array( 'label' => 'Show review button', 'type' => 'checkbox', 'default' => '1' ),
    );
}

function sscb_add_offer_meta_box() {
    add_meta_box( 'sscb_offer_meta', 'Coupon Offer Settings', 'sscb_render_offer_meta_box', 'ss_coupon_offer', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'sscb_add_offer_meta_box' );

function sscb_render_offer_meta_box( $post ) {
    wp_nonce_field( 'sscb_save_offer_meta', 'sscb_offer_nonce' );
    $fields = sscb_offer_fields();
    echo '<table class="form-table" role="presentation"><tbody>';
    foreach ( $fields as $key => $field ) {
        $value = get_post_meta( $post->ID, '_sscb_' . $key, true );
        if ( '' === $value ) {
            $value = $field['default'];
        }
        echo '<tr>';
        echo '<th scope="row"><label for="sscb_' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th>';
        echo '<td>';
        switch ( $field['type'] ) {
            case 'textarea':
                echo '<textarea class="large-text" rows="4" id="sscb_' . esc_attr( $key ) . '" name="sscb_' . esc_attr( $key ) . '">' . esc_textarea( $value ) . '</textarea>';
                break;
            case 'select':
                echo '<select id="sscb_' . esc_attr( $key ) . '" name="sscb_' . esc_attr( $key ) . '">';
                foreach ( $field['options'] as $option_value => $option_label ) {
                    echo '<option value="' . esc_attr( $option_value ) . '" ' . selected( $value, $option_value, false ) . '>' . esc_html( $option_label ) . '</option>';
                }
                echo '</select>';
                break;
            case 'checkbox':
                echo '<label><input type="checkbox" id="sscb_' . esc_attr( $key ) . '" name="sscb_' . esc_attr( $key ) . '" value="1" ' . checked( $value, '1', false ) . '> ' . esc_html__( 'Enabled', 'savesleuth-coupon-block' ) . '</label>';
                break;
            default:
                $input_type = ( 'url' === $field['type'] ) ? 'url' : 'text';
                echo '<input class="regular-text" type="' . esc_attr( $input_type ) . '" id="sscb_' . esc_attr( $key ) . '" name="sscb_' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
                break;
        }
        if ( 'coupon_code' === $key ) {
            echo '<p class="description">The public code users enter at checkout.</p>';
        }
        if ( 'affiliate_url' === $key ) {
            echo '<p class="description">Use your full affiliate URL here.</p>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    $slug = $post->post_name ? $post->post_name : 'offer-slug';
    echo '<hr><p><strong>Shortcode by ID:</strong> <code>[ss_coupon id="' . esc_html( $post->ID ) . '"]</code></p>';
    echo '<p><strong>Shortcode by slug:</strong> <code>[ss_coupon id="' . esc_html( $slug ) . '"]</code></p>';
}

function sscb_save_offer_meta( $post_id ) {
    if ( ! isset( $_POST['sscb_offer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sscb_offer_nonce'] ) ), 'sscb_save_offer_meta' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( 'ss_coupon_offer' !== get_post_type( $post_id ) ) {
        return;
    }

    $fields = sscb_offer_fields();
    foreach ( $fields as $key => $field ) {
        $meta_key = '_sscb_' . $key;
        if ( 'checkbox' === $field['type'] ) {
            $value = isset( $_POST['sscb_' . $key] ) ? '1' : '0';
        } else {
            $raw = isset( $_POST['sscb_' . $key] ) ? wp_unslash( $_POST['sscb_' . $key] ) : '';
            switch ( $field['type'] ) {
                case 'textarea':
                    $value = sanitize_textarea_field( $raw );
                    break;
                case 'url':
                    $value = esc_url_raw( $raw );
                    break;
                case 'select':
                    $value = sanitize_key( $raw );
                    break;
                default:
                    $value = sanitize_text_field( $raw );
            }
        }
        update_post_meta( $post_id, $meta_key, $value );
    }
}
add_action( 'save_post', 'sscb_save_offer_meta' );

function sscb_add_offer_columns( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'title' === $key ) {
            $new['sscb_store'] = 'Store';
            $new['sscb_code'] = 'Code';
            $new['sscb_discount'] = 'Discount';
            $new['sscb_shortcode'] = 'Shortcode';
        }
    }
    return $new;
}
add_filter( 'manage_ss_coupon_offer_posts_columns', 'sscb_add_offer_columns' );

function sscb_render_offer_columns( $column, $post_id ) {
    if ( 'sscb_store' === $column ) {
        echo esc_html( get_post_meta( $post_id, '_sscb_store_name', true ) );
    } elseif ( 'sscb_code' === $column ) {
        echo esc_html( get_post_meta( $post_id, '_sscb_coupon_code', true ) );
    } elseif ( 'sscb_discount' === $column ) {
        echo esc_html( get_post_meta( $post_id, '_sscb_discount', true ) );
    } elseif ( 'sscb_shortcode' === $column ) {
        echo '<code>[ss_coupon id="' . esc_html( $post_id ) . '"]</code>';
    }
}
add_action( 'manage_ss_coupon_offer_posts_custom_column', 'sscb_render_offer_columns', 10, 2 );

function sscb_get_offer_data( $identifier ) {
    $post = null;
    if ( is_numeric( $identifier ) ) {
        $post = get_post( (int) $identifier );
    } elseif ( is_string( $identifier ) && '' !== $identifier ) {
        $post = get_page_by_path( sanitize_title( $identifier ), OBJECT, 'ss_coupon_offer' );
    }

    if ( ! $post || 'ss_coupon_offer' !== $post->post_type ) {
        return null;
    }

    $data = array(
        'offerId'      => (int) $post->ID,
        'storeName'    => get_post_meta( $post->ID, '_sscb_store_name', true ),
        'discount'     => get_post_meta( $post->ID, '_sscb_discount', true ),
        'couponCode'   => strtoupper( (string) get_post_meta( $post->ID, '_sscb_coupon_code', true ) ),
        'affiliateUrl' => get_post_meta( $post->ID, '_sscb_affiliate_url', true ),
        'reviewUrl'    => get_post_meta( $post->ID, '_sscb_review_url', true ),
        'language'     => get_post_meta( $post->ID, '_sscb_language', true ),
        'variant'      => get_post_meta( $post->ID, '_sscb_variant', true ),
        'headline'     => get_post_meta( $post->ID, '_sscb_headline', true ),
        'description'  => get_post_meta( $post->ID, '_sscb_description', true ),
        'copyOpensTab' => '1' === get_post_meta( $post->ID, '_sscb_copy_opens', true ),
        'showReview'   => '1' === get_post_meta( $post->ID, '_sscb_show_review', true ),
        'title'        => $post->post_title,
        'slug'         => $post->post_name,
    );

    if ( '' === $data['storeName'] ) {
        $data['storeName'] = $post->post_title;
    }
    if ( '' === $data['discount'] ) {
        $data['discount'] = '10%';
    }
    if ( '' === $data['language'] ) {
        $data['language'] = 'de';
    }
    if ( '' === $data['variant'] ) {
        $data['variant'] = 'full';
    }

    return $data;
}

function sscb_get_offer_list_for_editor() {
    $posts = get_posts(
        array(
            'post_type'      => 'ss_coupon_offer',
            'post_status'    => 'publish',
            'numberposts'    => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'suppress_filters' => false,
        )
    );

    $offers = array();
    foreach ( $posts as $post ) {
        $offer = sscb_get_offer_data( $post->ID );
        if ( $offer ) {
            $offers[] = array(
                'id'         => $offer['offerId'],
                'label'      => $post->post_title . ' (#' . $post->ID . ')',
                'storeName'  => $offer['storeName'],
                'discount'   => $offer['discount'],
                'couponCode' => $offer['couponCode'],
                'language'   => $offer['language'],
                'variant'    => $offer['variant'],
                'headline'   => $offer['headline'],
                'description'=> $offer['description'],
            );
        }
    }
    return $offers;
}

function sscb_resolve_attributes( $attributes = array() ) {
    $defaults = array(
        'offerId'       => 0,
        'storeName'     => '',
        'discount'      => '10%',
        'couponCode'    => '',
        'affiliateUrl'  => '',
        'reviewUrl'     => '',
        'language'      => 'de',
        'variant'       => 'full',
        'headline'      => '',
        'description'   => '',
        'copyOpensTab'  => true,
        'showReview'    => true,
    );
    $atts = wp_parse_args( (array) $attributes, $defaults );

    if ( ! empty( $atts['offerId'] ) ) {
        $offer = sscb_get_offer_data( $atts['offerId'] );
        if ( $offer ) {
            $atts = array_merge( $atts, $offer );
        }
    }

    return $atts;
}

function sscb_enqueue_assets() {
    wp_enqueue_style( 'sscb-style' );
    wp_enqueue_script( 'sscb-view-script' );
}

function sscb_render_coupon( $attributes = array(), $content = '', $block = null ) {
    $atts = sscb_resolve_attributes( $attributes );
    $lang = sanitize_key( $atts['language'] );
    $labels = sscb_get_labels( $lang );

    $store_name    = sanitize_text_field( $atts['storeName'] );
    $discount      = sanitize_text_field( $atts['discount'] );
    $coupon_code   = strtoupper( sanitize_text_field( $atts['couponCode'] ) );
    $affiliate_url = esc_url( $atts['affiliateUrl'] );
    $review_url    = esc_url( $atts['reviewUrl'] );
    $variant       = in_array( $atts['variant'], array( 'full', 'compact' ), true ) ? $atts['variant'] : 'full';
    $headline      = sanitize_text_field( $atts['headline'] );
    $description   = sanitize_text_field( $atts['description'] );
    $copy_opens    = ! empty( $atts['copyOpensTab'] );
    $show_review   = ! empty( $atts['showReview'] );

    if ( '' === $store_name || '' === $coupon_code || '' === $affiliate_url ) {
        return '';
    }

    sscb_enqueue_assets();

    if ( '' === $headline ) {
        $headline = ( 'compact' === $variant ) ? $store_name : $labels['deal_tip'];
    }

    if ( '' === $description ) {
        if ( 'de' === $lang ) {
            $description = sprintf( '%s bietet aktuell %s mit dem Code %s.', $store_name, $discount . ' Rabatt', $coupon_code );
        } elseif ( 'fr' === $lang ) {
            $description = sprintf( '%s propose actuellement %s avec le code %s.', $store_name, $discount . ' de réduction', $coupon_code );
        } else {
            $description = sprintf( '%s currently offers %s off with the code %s.', $store_name, $discount, $coupon_code );
        }
    }

    $masked_code = sscb_mask_code( $coupon_code );
    $wrapper_classes = 'sscb-coupon sscb-variant-' . $variant;

    ob_start();
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes ); ?>" data-nosnippet>
        <div class="sscb-toprow">
            <div class="sscb-badges">
                <span class="sscb-discount"><?php echo esc_html( $discount ); ?> <?php echo esc_html( $labels['discount'] ); ?></span>
                <span class="sscb-status"><?php echo esc_html( $labels['active_code'] ); ?></span>
            </div>
            <?php if ( 'full' === $variant ) : ?>
                <div class="sscb-storemeta">
                    <span class="sscb-storelabel"><?php echo esc_html( $labels['shop'] ); ?>:</span>
                    <span class="sscb-storevalue"><?php echo esc_html( $store_name ); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="sscb-headline"><?php echo esc_html( $headline ); ?></div>
        <p class="sscb-description"><?php echo esc_html( $description ); ?></p>

        <div class="sscb-mainrow">
            <div class="sscb-codepanel">
                <div class="sscb-codewrap" tabindex="0" role="button" aria-label="<?php echo esc_attr( $labels['show_code'] ); ?>"
                    data-code="<?php echo esc_attr( $coupon_code ); ?>"
                    data-open-url="<?php echo esc_url( $affiliate_url ); ?>"
                    data-open-tab="<?php echo $copy_opens ? '1' : '0'; ?>"
                    data-copied-label="<?php echo esc_attr( $labels['code_copied'] ); ?>">
                    <span class="sscb-code-mask" aria-hidden="true"><?php echo esc_html( $masked_code ); ?></span>
                    <span class="sscb-code-full" hidden><?php echo esc_html( $coupon_code ); ?></span>
                    <span class="sscb-reveal-curtain" aria-hidden="true">
                        <span class="sscb-curtain-text"><?php echo esc_html( $labels['reveal_strip'] ); ?></span>
                    </span>
                </div>
            </div>

            <div class="sscb-actions">
                <a class="sscb-shop-link" href="<?php echo esc_url( $affiliate_url ); ?>" target="_blank" rel="sponsored nofollow">
                    <?php echo esc_html( $labels['go_shop'] ); ?>
                </a>

                <?php if ( $show_review && ! empty( $review_url ) ) : ?>
                    <a class="sscb-review-link" href="<?php echo esc_url( $review_url ); ?>">
                        <?php echo esc_html( $labels['read_review'] ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="sscb-feedback" aria-live="polite"></div>
        <div class="sscb-note"><?php echo esc_html( $labels['description'] ); ?></div>
    </div>
    <?php
    return ob_get_clean();
}

function sscb_register_block() {
    wp_register_script( 'sscb-editor-script', SSCB_URL . 'index.js', array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ), SSCB_VERSION, true );
    wp_register_script( 'sscb-view-script', SSCB_URL . 'view.js', array(), SSCB_VERSION, true );
    wp_register_style( 'sscb-style', SSCB_URL . 'style.css', array(), SSCB_VERSION );
    wp_register_style( 'sscb-editor-style', SSCB_URL . 'editor.css', array( 'sscb-style' ), SSCB_VERSION );

    wp_localize_script(
        'sscb-editor-script',
        'SSCBData',
        array(
            'offers' => sscb_get_offer_list_for_editor(),
        )
    );

    register_block_type(
        'savesleuth/coupon-block',
        array(
            'api_version'     => 3,
            'editor_script'   => 'sscb-editor-script',
            'editor_style'    => 'sscb-editor-style',
            'style'           => 'sscb-style',
            'script'          => 'sscb-view-script',
            'render_callback' => 'sscb_render_coupon',
            'attributes'      => array(
                'offerId'       => array( 'type' => 'number', 'default' => 0 ),
                'storeName'     => array( 'type' => 'string', 'default' => 'Happy 420' ),
                'discount'      => array( 'type' => 'string', 'default' => '10%' ),
                'couponCode'    => array( 'type' => 'string', 'default' => 'SAVESLEUTH' ),
                'affiliateUrl'  => array( 'type' => 'string', 'default' => 'https://happy-420.de/?ref=savesleuth' ),
                'reviewUrl'     => array( 'type' => 'string', 'default' => '/de/stores/happy-420/' ),
                'language'      => array( 'type' => 'string', 'default' => 'de' ),
                'variant'       => array( 'type' => 'string', 'default' => 'full' ),
                'headline'      => array( 'type' => 'string', 'default' => '' ),
                'description'   => array( 'type' => 'string', 'default' => '' ),
                'copyOpensTab'  => array( 'type' => 'boolean', 'default' => true ),
                'showReview'    => array( 'type' => 'boolean', 'default' => true ),
            ),
            'supports'        => array( 'html' => false ),
        )
    );
}
add_action( 'init', 'sscb_register_block' );

function sscb_shortcode( $atts ) {
    $mapped = shortcode_atts(
        array(
            'id'          => '',
            'store'       => '',
            'discount'    => '10%',
            'code'        => '',
            'url'         => '',
            'review_url'  => '',
            'lang'        => 'de',
            'variant'     => 'full',
            'headline'    => '',
            'description' => '',
        ),
        $atts,
        'ss_coupon'
    );

    if ( '' !== $mapped['id'] ) {
        $attrs = array( 'offerId' => $mapped['id'] );
        if ( ! empty( $mapped['lang'] ) ) {
            $attrs['language'] = $mapped['lang'];
        }
        if ( ! empty( $mapped['variant'] ) ) {
            $attrs['variant'] = $mapped['variant'];
        }
        if ( ! empty( $mapped['headline'] ) ) {
            $attrs['headline'] = $mapped['headline'];
        }
        if ( ! empty( $mapped['description'] ) ) {
            $attrs['description'] = $mapped['description'];
        }
        return sscb_render_coupon( $attrs );
    }

    return sscb_render_coupon(
        array(
            'storeName'    => $mapped['store'],
            'discount'     => $mapped['discount'],
            'couponCode'   => $mapped['code'],
            'affiliateUrl' => $mapped['url'],
            'reviewUrl'    => $mapped['review_url'],
            'language'     => $mapped['lang'],
            'variant'      => $mapped['variant'],
            'headline'     => $mapped['headline'],
            'description'  => $mapped['description'],
        )
    );
}
add_shortcode( 'ss_coupon', 'sscb_shortcode' );
