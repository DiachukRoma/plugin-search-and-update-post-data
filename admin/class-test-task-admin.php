<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://te
 * @since      1.0.0
 *
 * @package    Test_Task
 * @subpackage Test_Task/admin
 */
class Test_Task_Admin
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('wp_ajax_search_posts', [$this, 'search_posts_callback']);
		add_action('wp_ajax_nopriv_search_posts', [$this, 'search_posts_callback']);

		add_action('wp_ajax_set_new_data', [$this, 'set_new_data_callback']);
		add_action('wp_ajax_nopriv_set_new_data', [$this, 'set_new_data_callback']);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/test-task-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/test-task-admin.js', array('jquery'));
		wp_localize_script($this->plugin_name, 'admin_ajax', array('url' => admin_url('admin-ajax.php')));
	}

	public function add_admin_page()
	{
		add_menu_page('Post searcher', 'Post searcher', 'manage_options', 'post-searcher', array($this, 'post_searcher_page'));
	}

	public function post_searcher_page()
	{
		require_once plugin_dir_path(__FILE__) . 'partials/test-task-admin-display.php';
	}

	public static function search_posts()
	{
	}

	public function search_posts_callback()
	{
		global $wpdb;
		$search_word = $_POST['slug'];

		$sql_combined = $wpdb->prepare("
			SELECT DISTINCT p.*
			FROM " . $wpdb->prefix . "posts AS p
			LEFT JOIN " . $wpdb->prefix . "postmeta AS pm_title ON p.ID = pm_title.post_id AND pm_title.meta_key = '_yoast_wpseo_title'
			LEFT JOIN " . $wpdb->prefix . "postmeta AS pm_desc ON p.ID = pm_desc.post_id AND pm_desc.meta_key = '_yoast_wpseo_metadesc'
			WHERE p.post_type = 'post' AND p.post_status = 'publish'
			AND (
				p.post_title LIKE %s OR
				p.post_content LIKE %s OR
				pm_title.meta_value LIKE %s OR
				pm_desc.meta_value LIKE %s
			)
		", '%' . $wpdb->esc_like($search_word) . '%', '%' . $wpdb->esc_like($search_word) . '%', '%' . $wpdb->esc_like($search_word) . '%', '%' . $wpdb->esc_like($search_word) . '%');

		$combined_results = $wpdb->get_results($sql_combined);

		if (isset($combined_results)) {
			$content = '<table class="posts-table">
				<tr>
					<th>
						' . __('Title', 'tt') . '
						<hr>
						<form class="form-replace">
							<input type="text" name="data-post__title" placeholder="Enter replace title...">
							<button type="submit" class="button">Replace</button>
						</form>
					</th>
					<th>
						' . __('Content', 'tt') . '
						<hr>
						<form class="form-replace">
							<input type="text" name="data-post__content" placeholder="Enter replace content...">
							<button type="submit" class="button">Replace</button>
						</form>
					</th>
					<th>
						' . __('Meta title', 'tt') . '
						<hr>
						<form class="form-replace">
							<input type="text" name="data-post__wpseo_title" placeholder="Enter replace meta title...">
							<button type="submit" class="button">Replace</button>
						</form>
					</th>
					<th>
						' . __('Meta description', 'tt') . '
						<hr>
						<form class="form-replace">
							<input type="text" name="data-post__wpseo_metadesc" placeholder="Enter replace meta description...">
							<button type="submit" class="button">Replace</button>
						</form>
					</th>
				</tr>';

			foreach ($combined_results as $post) {
				$content .= '<tr data-id="' . $post->ID . '">
						<td class="data-post__title">' . $post->post_title . '</td>
						<td class="data-post__content">' . $post->post_content . '</td>
						<td class="data-post__wpseo_title">' . get_post_meta($post->ID, '_yoast_wpseo_title', true) . '</td>
						<td class="data-post__wpseo_metadesc">' . get_post_meta($post->ID, '_yoast_wpseo_metadesc', true) . '</td>
					</tr>';
			}

			$content .= '</table>';
			echo $content;
		} else {
			echo "<h4>Please, enter search word in form</h4>";
		}

		wp_die();
	}

	public function set_new_data_callback()
	{
		$field = $_POST['field'];
		$idx = $_POST['idx'];
		$value = $_POST['value'];

		$req = [];

		switch ($field) {
			case 'data-post__title':
				$req = ['post_title' => $value];
				break;
			case 'data-post__content':
				$req = ['post_content' => $value];
				break;
			case 'data-post__wpseo_title':
				$req = ['meta_input' => ['_yoast_wpseo_title' => $value]];
				break;
			case 'data-post__wpseo_metadesc':
				$req = ['meta_input' => ['_yoast_wpseo_metadesc' => $value]];
				break;
			default:
				return '';
		}

		foreach ($idx as $id) {
			$req['ID'] = $id;
			wp_update_post(wp_slash($req));
		}

		wp_die();
	}
}
