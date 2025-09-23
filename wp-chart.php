<?php
/**
 * Plugin Name:       WP Coffee Flavor Chart
 * Description:       Affiche des graphiques en toile d'araignée avec D3.js via un shortcode.
 * Version:           1.0.2
 * Author:            Jérémie Zarca
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mon_plugin_enqueue_scripts() {
    wp_enqueue_script('d3', 'https://d3js.org/d3.v7.min.js', array(), '7.0.0', true);
    wp_enqueue_script('spider-chart-script', plugin_dir_url( __FILE__ ) . 'js/spider-chart.js', array( 'd3' ), '1.1', true);
    wp_enqueue_style('spider-chart-style', plugin_dir_url( __FILE__ ) . 'css/style.css');
}
add_action( 'wp_enqueue_scripts', 'mon_plugin_enqueue_scripts' );

function validate_color( $color ) {
    if ( empty( $color ) ) {
        return null;
    }

    if ( preg_match( '/^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $color ) ) {
        return '#' . ltrim( $color, '#' );
    }

    $allowed_color_names = array(
        'aliceblue', 'antiquewhite', 'aqua', 'aquamarine', 'azure', 'beige', 'bisque', 'black', 'blanchedalmond', 'blue',
        'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate', 'coral', 'cornflowerblue', 'cornsilk',
        'crimson', 'cyan', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray', 'darkgreen', 'darkkhaki', 'darkmagenta',
        'darkolivegreen', 'darkorange', 'darkorchid', 'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray',
        'darkturquoise', 'darkviolet', 'deeppink', 'deepskyblue', 'dimgray', 'dodgerblue', 'firebrick', 'floralwhite',
        'forestgreen', 'fuchsia', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'gray', 'green', 'greenyellow', 'honeydew',
        'hotpink', 'indianred', 'indigo', 'ivory', 'khaki', 'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon',
        'lightblue', 'lightcoral', 'lightcyan', 'lightgoldenrodyellow', 'lightgray', 'lightgreen', 'lightpink', 'lightsalmon',
        'lightseagreen', 'lightskyblue', 'lightslategray', 'lightsteelblue', 'lightyellow', 'lime', 'limegreen', 'linen',
        'magenta', 'maroon', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple', 'mediumseagreen', 'mediumslateblue',
        'mediumspringgreen', 'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'moccasin',
        'navajowhite', 'navy', 'oldlace', 'olive', 'olivedrab', 'orange', 'orangered', 'orchid', 'palegoldenrod', 'palegreen',
        'paleturquoise', 'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'purple', 'rebeccapurple',
        'red', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna', 'silver',
        'skyblue', 'slateblue', 'slategray', 'snow', 'springgreen', 'steelblue', 'tan', 'teal', 'thistle', 'tomato',
        'turquoise', 'violet', 'wheat', 'white', 'whitesmoke', 'yellow', 'yellowgreen'
    );

    if ( in_array( strtolower( $color ), $allowed_color_names ) ) {
        return strtolower( $color );
    }

    return null;
}


function d3_spider_chart_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'data'     => 'Batterie:0.8,Design:0.9,Appareil Photo:0.6,Écran:0.7,Performance:0.95',
            'color'    => null,
            'linecolor'    => null,
            'gradient'  => null,
            'atts' => array()
        ),
        $atts,
        'd3_spider_chart'
    );

    $formatted_data = array();
    $data_pairs = explode( ',', $atts['data'] );
    foreach ( $data_pairs as $pair ) {
        $parts = explode( ':', $pair );
        if ( count( $parts ) === 2 ) {
            $formatted_data[] = array(
                "axis"  => sanitize_text_field( trim( $parts[0] ) ),
                "value" => floatval( trim( $parts[1] ) ),
            );
        }
    }

    $gradient_colors = null;
    if ( ! is_null( $atts['gradient'] ) ) {
        $clean_gradient_str = str_replace( array('[', ']', ' '), '', $atts['gradient'] );
        $colors = explode(',', $clean_gradient_str);
        
        if ( !empty($colors) ) {
            $validated_colors = array_map('validate_color', $colors);
            
            $gradient_colors = array_filter($validated_colors);

            if(count($gradient_colors) !== 2) {
                $gradient_colors = null;
            }
        }
    }

    if ( ! empty( $formatted_data ) ) {
        $chart_id = 'spider-chart-container-' . uniqid();

        $chart_options = array(
            'data'     => array($formatted_data),
            'color'    => validate_color($atts['color']),
            'linecolor'    => validate_color($atts['linecolor']),
            'gradient' => $gradient_colors,
            'atts' => $atts
        );

        wp_add_inline_script(
            'spider-chart-script',
            'document.addEventListener("DOMContentLoaded", function() { drawSpiderChart("#' . esc_js($chart_id) . '", ' . wp_json_encode($chart_options, JSON_PRETTY_PRINT) . '); });'
        );
        
        return '<div id="' . esc_attr($chart_id) . '"></div>';
    }

    return '<p>Les données du graphique ne sont pas au bon format.</p>';
}
add_shortcode( 'd3_spider_chart', 'd3_spider_chart_shortcode' );