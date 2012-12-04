<?php

/**
 * The THATCamp Network Posts widget
 */
class THATCamp_Network_Posts extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'thatcamp_network_posts',
			'THATCamp Post Browser',
			array(
				'description' => 'A category browser for posts from across the THATCamp network.',
			)
		);
	}

	/**
	 * Don't think we need any options
	 */
	public function form() {}
	public function update() {}

	public function widget( $args, $instance ) {
		if ( ! function_exists( 'get_sitewide_tags_option' ) ) {
			return;
		}

		extract( $args );

		$tags_blog_id = get_sitewide_tags_option( 'tags_blog_id' );

		switch_to_blog( $tags_blog_id );

		$title = 'Browse THATCamp Posts';
		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] ) ? '1' : '0';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h);

		if ( $d ) {
			$cat_args['show_option_none'] = __('Select Category');
			wp_dropdown_categories(apply_filters('widget_categories_dropdown_args', $cat_args));
?>

<script type='text/javascript'>
/* <![CDATA[ */
	var dropdown = document.getElementById("cat");
	function onCatChange() {
		if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo home_url(); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
		}
	}
	dropdown.onchange = onCatChange;
/* ]]> */
</script>

<?php
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';

		// We need to filter the category links, but only right here
		add_filter( 'term_link', array( $this, 'filter_term_link' ), 10, 3 );

		wp_list_categories(apply_filters('widget_categories_args', $cat_args));

		remove_filter( 'term_link', array( $this, 'filter_term_link' ), 10, 3 );
?>
		</ul>
<?php
		}

		echo $after_widget;

		restore_current_blog();
	}

	function filter_term_link( $term_link, $term, $taxonomy ) {
                global $wpdb;
                $proceedings_blog_id = $wpdb->get_var( "SELECT blog_id FROM $wpdb->blogs WHERE domain LIKE 'proceedings%'" );
		$term_link  = trailingslashit( get_blog_option( $proceedings_blog_id, 'home' ) );
		$term_link .= 'stream/';
		$term_link  = add_query_arg( 'category', $term->slug, $term_link );
		return $term_link;
	}
}
register_widget( 'THATCamp_Network_Posts' );

/**
 * The THATCamp Network Posts widget
 */
class THATCamp_Network_Search extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'thatcamp_network_search',
			'THATCamp Network Search',
			array(
				'description' => 'Search the entire THATCamp network.',
			)
		);
	}

	/**
	 * Don't think we need any options
	 */
	public function form() {}
	public function update() {}

	public function widget( $args, $instance ) {
		$potc_url = $this->proceedings_link();

		extract( $args );

		switch_to_blog( $tags_blog_id );

		$title = 'Search';
		$value = ! empty( $_GET['tcs'] ) ? urldecode( $_GET['tcs'] ) : '';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		?>

		<form method="get" action="<?php echo $potc_url . 'stream/' ?>">
			<input id="thatcamp_network_search" name="tcs" value="<?php echo esc_attr( $value ) ?>" />
		</form>

		<?php
		echo $after_widget;

	}

	function proceedings_link() {
                global $wpdb;
                $proceedings_blog_id = $wpdb->get_var( "SELECT blog_id FROM $wpdb->blogs WHERE domain LIKE 'proceedings%'" );
		$term_link  = trailingslashit( get_blog_option( $proceedings_blog_id, 'home' ) );
		return $term_link;
	}
}
register_widget( 'THATCamp_Network_Search' );
