<?php
/*
 * Plugin Name: JonTag
 * Plugin URI: http://www.github.com/jonkarlen78/jon-tag
 * Description: Plugin to allow designation of a primary tag per post
 * Version 0.1
 * Author: Jon Karlen
 */

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

if( ! class_exists('JonTag') )
{
    class JonTag
    {
        /**
         * @var string
         */
        protected $tag = 'jon-tag';

        /**
         * @var string
         */
        protected $name = 'JonTag';

        /**
         * @var string
         */
        protected $version = '0.1';

        /**
         * Settings array adding settings to the admin panel
         * @var array
         */
        protected $settings = [
          'css_class' => [
              'description' => 'CSS class to display the primary tag in the theme with',
              'default' => 'primary',
              'title' => 'CSS Class',
          ]
        ];

        /**
         * Constructor
         * Registers admin actions , sets up dependencies, adds actions
         */
        public function __construct()
        {
            add_shortcode( $this->tag, [ &$this, 'shortcode' ] );

            if ( is_admin() ) {
                add_action( 'admin_init', [ &$this, 'settings' ] );
                wp_enqueue_script('', '/wp-content/plugins/jon-tag/jon-tag.js');
            }

            if ( $options = get_option( $this->tag ) ) {
                $this->options = $options;
            }

            add_action('add_meta_boxes', [ &$this, 'meta_box_init'] );
            add_action('save_post', [ &$this, 'save_featured_tag_box'] );
            add_filter('the_title', [ &$this, 'tag_the_title'], 1, 2 );
        }

        /**
         * Registered action for add_meta_boxes action
         */
        public function meta_box_init()
        {
            add_meta_box(
                'featured-tag-meta-box',
                'Select Featured Tag',
                [ &$this, 'draw_featured_tag_box'],
                'post',
                'side',
                'high'
            );
        }

        /**
         * Callback to add the meta box
         */
        public function draw_featured_tag_box()
        {
            global $post_id;
            $existing_tags = wp_get_post_tags( $post_id );
            $selected_tag = get_post_meta($post_id, '_featured_tag', true);

            ?>
            <select name="featured_tag">
                <option value=''>-- NONE --</option>
                <?php foreach( $existing_tags as $tag ) :
                    $selected = $selected_tag == $tag->term_id ? ' selected' : '';
                    ?>
                    <option value="<?= $tag->term_id ?>"<?= $selected ?>><?= $tag->name ?></option>
                <?php endforeach; ?>
            </select>
            <?php
        }

        /**
         * Callback for save_post
         */
        public function save_featured_tag_box()
        {
            global $post_id;
            update_post_meta( $post_id, '_featured_tag', $_POST['featured_tag'] );
        }

        /**
         * Override for the_title() to show the featured tag
         * @param $title
         * @param null $id
         * @return string
         */
        public function tag_the_title($title, $id = null)
        {
            if( ! in_the_loop() || ! $id )
            {
                return $title;
            }

            if( $term_id =  get_post_meta($id, '_featured_tag', true) )
            {
                $primary_tag = ' <span class="tags-links">(' . get_term($term_id, 'post_tag')->name . ')</span>';
            } else {
                $primary_tag = '';
            }
            return $title . $primary_tag;
        }

        /**
         * Add the setting fields to the Reading settings page.
         *
         * @access public
         */
        public function settings()
        {
            $section = 'reading';
            add_settings_section(
                $this->tag . '_settings_section',
                $this->name . ' Settings',
                function () {
                    echo '<p>Configuration options for the ' . esc_html( $this->name ) . ' plugin.</p>';
                },
                $section
            );

            foreach ( $this->settings as $id => $options ) {
                $options['id'] = $id;
                add_settings_field(
                    $this->tag . '_' . $id . '_settings',
                    $options['title'],
                    [ &$this, 'settings_field' ],
                    $section,
                    $this->tag . '_settings_section',
                    $options
                );
            }

            register_setting(
                $section,
                $this->tag
            );
        }

        /**
         * Defines settings to be added to the reading config
         * @param array $options
         */
        public function settings_field( array $options = [] )
        {
            $atts = array(
                'id' => $this->tag . '_' . $options['id'],
                'name' => $this->tag . '[' . $options['id'] . ']',
                'type' => ( isset( $options['type'] ) ? $options['type'] : 'text' ),
                'class' => 'medium-text',
                'value' => ( array_key_exists( 'default', $options ) ? $options['default'] : null ),
            );
            if ( isset( $this->options[$options['id']] ) ) {
                $atts['value'] = $this->options[$options['id']];
            }
            if ( isset( $options['placeholder'] ) ) {
                $atts['placeholder'] = $options['placeholder'];
            }
            if ( isset( $options['type'] ) && $options['type'] == 'checkbox' ) {
                if ( $atts['value'] ) {
                    $atts['checked'] = 'checked';
                }
                $atts['value'] = true;
            }
            array_walk( $atts, function( &$item, $key ) {
                $item = esc_attr( $key ) . '="' . esc_attr( $item ) . '"';
            } );
            ?>
            <label>
                <input <?php echo implode( ' ', $atts ); ?> />
                <?php if ( array_key_exists( 'description', $options ) ) : ?>
                    <?php esc_html_e( $options['description'] ); ?>
                <?php endif; ?>
            </label>
        <?php
        }
    }
    new JonTag();
}
