<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://te
 * @since      1.0.0
 *
 * @package    Test_Task
 * @subpackage Test_Task/admin/partials
 */

$query = Test_Task_Admin::search_posts()
?>

<div class="wrap">
    <div class="wrap__container">
        <h1 class="wp-heading-inline">Post editor</h1>

        <form id="search-form" class="posts-form">
            <input type="text" class="posts__text" name="posts__text" placeholder="Enter text...">
            <button type="submit" class="button">Submit</button>
        </form>
    </div>

    <div class="table-result"></div>
</div>