<?php

if ( ! function_exists( 'red_book_tax' ) ) {

// Опишем требуемый функционал
    function red_book_tax() {

        $labels = array(
            'name'                       => _x( 'Категории Красной книги', 'Taxonomy General Name', 'red_book' ),
            'singular_name'              => _x( 'Категория Красной книги', 'Taxonomy Singular Name', 'red_book' ),
            'menu_name'                  => __( 'Категории', 'red_book' ),
            'all_items'                  => __( 'Категории', 'red_book' ),
            'parent_item'                => __( 'Родительская категория Книги', 'red_book' ),
            'parent_item_colon'          => __( 'Родительская категория Книги:', 'red_book' ),
            'new_item_name'              => __( 'Новая категория', 'red_book' ),
            'add_new_item'               => __( 'Добавить новую категорию', 'red_book' ),
            'edit_item'                  => __( 'Редактировать категорию', 'red_book' ),
            'update_item'                => __( 'Обновить категорию', 'red_book' ),
            'search_items'               => __( 'Найти', 'red_book' ),
            'add_or_remove_items'        => __( 'Добавить или удалить категорию', 'red_book' ),
            'choose_from_most_used'      => __( 'Поиск среди популярных', 'red_book' ),
            'not_found'                  => __( 'Не найдено', 'red_book' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
        );
        register_taxonomy( 'red_book_tax', array( 'red_book' ), $args );

    }

    add_action( 'init', 'red_book_tax', 0 ); // инициализируем

}