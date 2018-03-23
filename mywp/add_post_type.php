<?php

if ( ! function_exists( 'red_book_cp' ) ) {

// Опишем требуемый функционал
    function red_book_cp() {

        $labels = array(
            'name'                => _x( 'Красная книга', 'Post Type General Name', 'red_book' ),
            'singular_name'       => _x( 'Красная книга', 'Post Type Singular Name', 'red_book' ),
            'menu_name'           => __( 'Красная Книга', 'red_book' ),
            'parent_item_colon'   => __( 'Родительский:', 'red_book' ),
            'all_items'           => __( 'Все записи', 'red_book' ),
            'view_item'           => __( 'Просмотреть', 'red_book' ),
            'add_new_item'        => __( 'Добавить новую запись в Красную Книгу', 'red_book' ),
            'add_new'             => __( 'Добавить новую', 'red_book' ),
            'edit_item'           => __( 'Редактировать запись', 'red_book' ),
            'update_item'         => __( 'Обновить запись', 'red_book' ),
            'search_items'        => __( 'Найти запись', 'red_book' ),
            'not_found'           => __( 'Не найдено', 'red_book' ),
            'not_found_in_trash'  => __( 'Не найдено в корзине', 'red_book' ),
        );
        $args = array(
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'excerpt', ),
            'taxonomies'          => array( 'red_book_tax' ), // категории, которые мы создадим ниже
            'public'              => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-book',
        );
        register_post_type( 'red_book', $args );

    }

    add_action( 'init', 'red_book_cp', 0 ); // инициализируем

}