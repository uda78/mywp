<?php
/*
Plugin Name: Options Page Example
Plugin URI: http://druweb.ru/wordpress-options-page.html
Description: Пример создания страницы с настройками.
Author URI: http://druweb.ru
Author: DruWeb.
Version: 1.0.0
*/

$ope_name = "Options Page Example";

function ope_code_add_admin() {
    global $ope_name;
    add_options_page(__('Settings').': '.$ope_name, $ope_name, 'edit_themes', basename(__FILE__), 'ope_code_to_admin');
}

// Вид административной страницы и обработка-запоминание получаемых опций

function ope_code_to_admin() {
    global $ope_name;
    ?>

    <div class="wrap">
        <?php
        screen_icon(); // Значок сгенерируется автоматически
        echo '<h2>'.__('Settings').': '.$ope_name.'</h2>'; // Заголовок
        // Пошла обработка запроса
        if (isset($_POST['save'])) {
            update_option('ope_textarea', stripslashes($_POST['textarea']));
            update_option('ope_textfield', stripslashes($_POST['textfield']));
            if (isset($_POST['checkbox']))
                update_option('ope_checkbox', 1);
            else
                update_option('ope_checkbox', 0);
            echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><b>'.__('Settings saved.').'</b></p></div>';
        }
        // Внешний вид формы
        ?>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Textarea:</th>
                    <td>
                        <textarea name="textarea" class="large-text code" type="textarea" cols="50" rows="11"><?php echo get_option('ope_textarea'); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Textfield:</th>
                    <td>
                        <input name="textfield" class="regular-text" type="text" value="<?php echo get_option('ope_textfield'); ?>" >
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Checkbox:</th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span>Checkbox</span>
                            </legend>
                            <label for="users_can_register">
                                <input name="checkbox" id="users_can_register" type="checkbox" value="1" <?php if(get_option('ope_checkbox')==1) { echo 'checked="checked"'; } ?>>
                                Поставьте здесь галочку
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
            <div class="submit">
                <input name="save" type="submit" class="button-primary" value="<?php echo __('Save Draft'); ?>" />
            </div>
        </form>

    </div>
    <?php
}

// Итоговые действия

add_action('admin_menu', 'ope_code_add_admin');

// Никаких следов после деинсталляции
// О работе хука я уже писал в одной из своих предыдущих записей

if (function_exists('register_uninstall_hook'))
    register_uninstall_hook(__FILE__, 'ope_deinstall');

function ope_deinstall() {
    delete_option('ope_textarea');
    delete_option('ope_textfield');
    delete_option('ope_checkbox');
}

?>