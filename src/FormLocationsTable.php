<?php
/**
 * Handles displaying the form locations.
 *
 * @package TARecord/LocationsAddonForGravityForms
 */

namespace TARecord\LocationAddonForGravityForms;

use GFAPI;
use WP_List_Table;

/**
 * Sets up the table for displaying form locations.
 */
class FormLocationsTable extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {

		parent::__construct(
			[
				'singular' => __( 'Location', 'location-add-on-for-gravity-forms' ),
				'plural'   => __( 'Locations', 'location-add-on-for-gravity-forms' ),
				'ajax'     => false, // should this table support ajax?
			]
		);

	}

	/**
	 * Retrieve customer’s data from the database.
	 *
	 * @return array An array of data for the table.
	 */
	public function get_locations() {

		global $wpdb;

		$form_id = absint( filter_input( INPUT_GET, 'form_id', FILTER_VALIDATE_INT ) );
		$orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
		$order   = strtoupper( filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING ) ?? 'asc' );

		$sql  = "SELECT * FROM {$wpdb->prefix}lagf_form_page";
		$args = [];

		if ( ! empty( $form_id ) ) {

			$sql   .= ' WHERE form_id = %d';
			$args[] = $form_id;
		}

		if ( ! empty( $orderby ) && ! empty( $order ) ) {

			$sql   .= ' ORDER BY %s %s';
			$args[] = $orderby;
			$args[] = $order;
		}

		$result = $wpdb->get_results(
			$wpdb->prepare( $sql, $args ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			'ARRAY_A'
		);

		return $result;
	}

	/**
	 * Set up the form_id column
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	public function column_form_id( $item ) {

		$gf_api = new GFAPI();
		$form   = $gf_api::get_form( $item['form_id'] );

		// Form does not exist.
		if ( false === $form ) {
			return '<span style="color:red">Invalid Form ID Used</span>';
		}

		$edit_url = admin_url( '?page=gf_edit_forms&id=' . $form['id'] );

		return '<a href="' . esc_url( $edit_url ) . '">' . esc_html( $form['title'] ) . '</a>';
	}

	/**
	 * Set up the post_id column
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	public function column_post_id( $item ) {

		$post = get_post( $item['post_id'] );

		return $post->post_title;
	}

	/**
	 * Set up the post_status column
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	public function column_post_status( $item ) {

		$post = get_post( $item['post_id'] );

		if ( 'publish' === $post->post_status || 'future' === $post->post_status || 'pending' === $post->post_status ) {

			return '<span style="color: #21759B;">Published</span>';

		} elseif ( 'draft' === $post->post_status || 'auto-draft' === $post->post_status ) {

			return 'Draft';

		} elseif ( 'private' === $post->post_status ) {

			return '<span style="color: #21759B;">Private</span>';

		} elseif ( 'trash' === $post->post_status ) {

			return '<span style="color: red;">Trashed</span>';

		}

	}

	/**
	 * Set up the links in the post_actions column
	 *
	 * @param object $item The current item.
	 *
	 * @return string
	 */
	public function column_post_actions( $item ) {

		$post = get_post( $item['post_id'] );

		return '<a href="post.php?post=' . $post->ID . '&action=edit">Edit</a> | <a href="' . get_post_permalink( $post->ID ) . '">View</a>';
	}

	/**
	 * Define all column names
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'form_id'      => __( 'Form Title', 'location-add-on-for-gravity-forms' ),
			'post_id'      => __( 'Post Title', 'location-add-on-for-gravity-forms' ),
			'post_status'  => __( 'Post Status', 'location-add-on-for-gravity-forms' ),
			'post_actions' => __( 'Post Actions', 'location-add-on-for-gravity-forms' ),
		];
		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = [
			'post_id' => [ 'post_id', true ],
			'form_id' => [ 'form_id', false ],
		];

		return $sortable_columns;
	}

	/**
	 * Prepare the items for display
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$sortable              = $this->get_sortable_columns();
		$hidden                = [];
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$per_page     = 25;
		$current_page = $this->get_pagenum();
		$data         = $this->get_locations( $per_page, $current_page );
		$total_items  = count( $data );

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		$this->items = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
	}

}
