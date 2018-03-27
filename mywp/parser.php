<?php

require 'vendor/autoload.php';
require 'simple_html_dom.php';

if ( !defined('ABSPATH') ) {
    /** Set up WordPress environment */
    require_once( dirname( __FILE__ ) . '/wp-load.php' );
}

class Parser{

    public function __construct()
    {
        $this->parseRootCategories();
    }

    public function sendRequest($url)
    {
        $client = new \GuzzleHttp\Client();

        if($client->get($url)->getStatusCode()==200)
        {
            $res = $client->request('GET', $url);
            $res->getHeaderLine('content-type');
            return str_get_html($res->getBody());
        }
        return '';
    }

    public function parseRootCategories()
    {
        $this->delAllProducts();

        $html = $this->sendRequest('http://www.vse-kraski.ru/catalog/');
        //$html = $this->sendRequest('http://www.vse-kraski.ru/catalog/ognebiozaschitnie_propitki/');

        foreach( $html->find('div#types_menu a') as $element )
        {
            $rootTerm = $this->addTerm(
                $element->plaintext,
                [
                    'parent' => 0,
                    'slug' => self::uniqueTermSlug($element->plaintext)
                ]
            );
            $this->parseSubCategories($element, $rootTerm['term_id']);
        }
    }

    public function parseSubCategories($link, $term_id )
    {
        $html1 = $this->sendRequest($link->href);

        foreach ($html1->find('a') as $link1)
        {
            if ($link1->style == "color:#0E5897; border-bottom:2px #0E5897 solid; text-transition:uppercase; font-size:14px; font-weight:bold; font-family:tahoma;") {

                // description
                $row = strip_tags($link1->parent()->parent()->outertext, '<b>');
                $str = strpos($row, "</b>");
                $str = substr($row, $str+4 );
                $description = str_replace('  ', '', $str);

                $subTerm = $this->addTerm(
                    $link1->plaintext,
                    [
                        'slug' => self::uniqueTermSlug($link1->plaintext),
                        'parent'=>$term_id,
                        'description' => $description
                    ]
                );


                if(is_wp_error($subTerm))
                {
                    print_r($subTerm);
                }
                echo "Data " . $link1->href . " " . $subTerm['term_id'] . "\n";

                $this->parseProducts($link1->href, $subTerm['term_id']);
            }
        }
    }

    public function parseProducts($link, $term_id)
    {

        $htmp = $this->sendRequest($link);

        if(empty($htmp)){ return;}

        foreach ( $htmp->find('#myTable tr') as $elems)
        {

            $product = [
                'post_title' => '',
                'meta' => [
                    '_packaging' => '',
                    '_manufacturer' => '',
                    '_class' => '',
                    '_price' => '',
                    '_regular_price' => '',
                ]
            ];

            if(count($elems->find('td')) == 6) {

                foreach ($elems->find('td') as $key => $elem) {

                    switch ($key) {
                        case 0:
                            $product['post_title'] = $elem->plaintext;
                            break;
                        case 1:
                            $product['meta']['_packaging'] = $elem->plaintext;
                            break;
                        case 2:
                            $product['meta']['_manufacturer'] = $elem->plaintext;
                            break;
                        case 3:
                            $product['meta']['_class'] = $elem->plaintext;
                            break;
                        case 5:
                            $arr = explode("&nbsp;", $elem->plaintext);
                            $product['meta']['_price'] = $arr[0];
                            $product['meta']['_regular_price'] = $arr[0];
                            break;
                        default:
                            //nothing
                            break;
                    }
                }

                $this->addProduct($product, $term_id);
            }
        }
    }

    public function addProduct($product, $term_id)
    {
        $prodID = wp_insert_post(
            [
                'post_title' => $product['post_title'],
                'post_type' => 'product',
                'post_author' => 1,
                'post_status' => 'publish'
            ],
            true
        );

        if( is_wp_error($prodID) ){
            echo $prodID->get_error_message();
            return;
        }

        foreach ($product['meta'] as $key=>$item)
        {
            update_post_meta( $prodID, $key, $item );
        }

        wp_set_object_terms( $prodID, (int)$term_id, 'product_cat' );
        echo $prodID . "\n";
    }

    public function addTerm( $name, $param = [])
    {
        $resp = wp_insert_term( $name, 'product_cat', $param);
        echo $name . "\n";
        return $resp;
    }

    public function delAllProducts()
    {
        //delete all produsts
        $allProd = get_posts(['post_type'=>'product', 'numberposts'=> 1000]);

        foreach ( $allProd as $item){
            wp_delete_post($item->ID);
        }

        //delete term
        $terms = get_terms( 'product_cat', array(
            'hide_empty' => false,
        ) );

        foreach ( $terms as $item)
        {
            wp_delete_term( $item->term_id, 'product_cat' );
            echo $item->term_id . "\n";
        }

    }

    public static function uniqueTermSlug($slug, $term = null)
    {
        global $wpdb;

        $slug = \sanitize_title($slug);

        if (!\term_exists($slug)) {
            return $slug;
        }

        if ($term) {
            if (\is_taxonomy_hierarchical($term->taxonomy) && !empty($term->parent)) {
                $the_parent = $term->parent;

                while (!empty($the_parent)) {
                    $parent_term = \get_term($the_parent, $term->taxonomy);

                    if (\is_wp_error($parent_term) || empty($parent_term)) {
                        break;
                    }

                    $slug .= '-' . $parent_term->slug;

                    if (!\term_exists($slug)) {
                        return $slug;
                    }

                    if (empty($parent_term->parent)) {
                        break;
                    }

                    $the_parent = $parent_term->parent;
                }
            }

            // If we didn't get a unique slug, try appending a number to make it unique.
            if (!empty($term->term_id)) {
                $query = $wpdb->prepare("SELECT `slug` FROM $wpdb->terms WHERE `slug` = '%s' AND `term_id` != %d", $slug, $term->term_id);
            } else {
                $query = $wpdb->prepare("SELECT `slug` FROM $wpdb->terms WHERE `slug` = '%s'", $slug);
            }

            if ($wpdb->get_var($query)) {
                $num = 2;

                do {
                    $alt_slug = $slug . "-$num";
                    $num++;
                    $slug_check = $wpdb->get_var($wpdb->prepare("SELECT `slug` FROM `{$wpdb->terms}` WHERE `slug` = '%s'", $alt_slug));
                } while ($slug_check);

                $slug = $alt_slug;
            }

        } else {
            $check_sql = "SELECT `slug` FROM `{$wpdb->terms}` WHERE `slug` = '%s' LIMIT 1";
            $slug_check = $wpdb->get_var($wpdb->prepare($check_sql, $slug));

            if ($slug_check) {
                $num = 2;

                do {
                    $alt_slug = $slug . "-$num";
                    $num++;
                    $slug_check = $wpdb->get_var($wpdb->prepare("SELECT `slug` FROM `{$wpdb->terms}` WHERE `slug` = '%s'", $alt_slug));
                } while ($slug_check);

                $slug = $alt_slug;
            }
        }

        return $slug;
    }
}

$pars = new Parser();
