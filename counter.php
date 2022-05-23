<?php

/**
 * Plugin Name:       Plugin Word Counter
 * Plugin URI:        https://andrevega.com
 * Description:       Shows the number of words, characters and reader time in each post.
 * Version:           0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Andrés Vega
 * Author URI:        https://author.example.com/
 * Text Domain:       wcpdomain
 * Domain Path:       /languages
 */


define('WORD_COUNTER_PATH', plugin_dir_path((__FILE__)));
require_once WORD_COUNTER_PATH . '/public/public-index.php';
require_once WORD_COUNTER_PATH . '/admin/admin-index.php';

class WordCountAndTimePlugin
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'adminPage'));

        add_action('admin_init', array($this, 'settings'));

        add_filter('the_content', array($this, 'ifWrap'));

        add_action('init', array($this, 'languages'));
    }

    function languages()
    {
        load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function ifWrap($content)
    {
        if (
            is_main_query() and is_single() and
            (get_option('wcp_wordcount', '1') or
                get_option('wcp_charactercount', '1') or get_option('wcp_readtime', '1'))
        ) {
            return $this->createHTML($content);
        }
        return $content;
    }

    function createHTML($content)
    {
        $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . '</h3><p>';

        //get word count once because both wordcount and read time will be need it.
        if (get_option('wcp_wordcount', '1') or get_option('wcp_readtime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if (get_option('wcp_wordcount', '1')) {
            $html .= esc_html__('This post has', 'wcpdomain') . ' ' . $wordCount . ' ' . esc_html__('words', 'wcpdomain') . '.<br>';
        }
        if (get_option('wcp_charactercount', '1')) {
            $html .= esc_html__('This post has', 'wcpdomain') . ' ' . strlen(strip_tags($content)) . ' ' . esc_html__('characters', 'wcpdomain') . '.<br>';
        }

        if (get_option('wcp_readtime', '1')) {
            $html .= esc_html__('This post will take about', 'wcpdomain') . ' ' . round($wordCount / 225) . ' ' . esc_html__('minute(s) to read', 'wcpdomain') . '.<br>';
        }

        $html .= '<p/>';

        if (get_option('wcp_location', '0') == '0') {
            return $html . $content;
        }
        return $content . $html;
    }

    function settings()
    {
        add_settings_section('wcp_first_section', null, null, 'word-count-admin-page');

        // Field Location 
        add_settings_field('wcp_location', esc_html__('Display Location', 'wcpdomain'), array($this, 'locationHTML'), 'word-count-admin-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

        // Field Headline
        add_settings_field('wcp_headline', esc_html__('Headline Text', 'wcpdomain'), array($this, 'headlineHTML'), 'word-count-admin-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statictis'));

        // Field WordCount + CharacterCount + ReadTime
        add_settings_field('wcp_wordcount', esc_html__('Word Count', 'wcpdomain'), array($this, 'checkboxHTML'), 'word-count-admin-page', 'wcp_first_section', array('theName' => 'wcp_wordcount'));
        register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        add_settings_field('wcp_charactercount', esc_html__('Character Count', 'wcpdomain'), array($this, 'checkboxHTML'), 'word-count-admin-page', 'wcp_first_section', array('theName' => 'wcp_charactercount'));
        register_setting('wordcountplugin', 'wcp_charactercount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        add_settings_field('wcp_readtime', esc_html__('Read Time', 'wcpdomain'), array($this, 'checkboxHTML'), 'word-count-admin-page', 'wcp_first_section', array('theName' => 'wcp_readtime'));
        register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    }

    // Select validation
    function sanitizeLocation($input)
    {
        if ($input != '0' and $input != '1') {
            add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be either Beginning of post or End or post');
            return get_option('wcp_location');
        }
        return $input;
    }
    // Field Headline
    function headlineHTML()
    { ?>
        <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?> ">
    <?php }

    // Field Location 
    function locationHTML()
    { ?>
        <select name="wcp_location">
            <option value="0" <?php selected(get_option('wcp_location'), '0') ?>><?php echo esc_html__('Beginning of post', 'wcpdomain') ?></option>
            <option value="1" <?php selected(get_option('wcp_location'), '1') ?>><?php echo esc_html__('End of post', 'wcpdomain') ?></option>
        </select>
    <?php }

    // CheckboxHTML
    function checkboxHTML($args)
    { ?>
        <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName']), '1') ?>>
    <?php }


    function adminPage()
    {
        add_options_page('Word Count Settings', esc_html__('Word Counter', 'wcpdomain'), 'manage_options', 'word-count-admin-page', array($this, 'AdminPageHTML'));
    }

    function AdminPageHTML()
    { ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Word Count Settings', 'wcpdomain'); ?></h1>
            <form action="options.php" method="POST">
                <?php
                settings_fields('wordcountplugin');
                do_settings_sections('word-count-admin-page');
                submit_button();
                ?>
            </form>
        </div>
<?php }
}
new WordCountAndTimePlugin();
