<?php
/**
 * Jewish Permalink Class
 *
 * Adds the ability to have permalinks with the Hebrew Date
 */
class JT_Hebrew_Date_Permalinks {
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ) );
		add_filter( 'pre_get_posts', array( __CLASS__, 'hebrew_date' ) );
		add_filter( 'get_the_archive_title', array( __CLASS__, 'archive_title' ), 11 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_filter( 'get_the_date', array( __CLASS__, 'get_the_date' ), 12, 3 );
	}

	public static function plugins_loaded() {
		self::rewrite_rules();
	}

	public static function query_vars( $var ) {
		$var[] = 'jthdate';
		return $var;
	}

	public static function rewrite_rules() {
	}

	public static function get_the_date( $the_date, $format, $post ) {
		// If this is a constant then do not reinterpret.
		$formats = apply_filters( 'jewish_time_ignore_date_formats', array( 'U', 'c', 'r', 'DATE_ATOM', 'DATE_COOKIE', 'DATE_ISO8601', 'DATE_W3C', 'DATE_RSS', 'DATE_RFC822', 'DATE_RC850', 'DATE_RFC1036', 'DATE_RFC1123', 'DATE_RFC7231', 'DATE_RFC2822', 'DATE_RFC3339', 'DATE_RFC3339_EXTENDED' ) );
		if ( in_array( $format, $formats, true ) ) {
			return $the_date;
		}

		// If this query variable is set then show the Hebrew version of the post time.
		if ( get_query_var( 'jthdate' ) ) {
			$hdate = new JT_Hebrew_Date( get_post_datetime( $post ) );
			return $hdate->get_the_date( $format );
		}

		// If the format includes this symbol, afterward have it reinterpreted as Hebrew
		$pieces = explode( '#', $format );
		if ( 2 === count( $pieces ) ) {
			$hdate = new JT_Hebrew_Date( get_post_datetime( $post ) );
			return get_the_date( $pieces[0], $post ) . $hdate->get_the_date( $pieces[1] );
		}

		return $the_date;
	}

	/*
	 * Converts a Hebrew Year, Month, and/or Day into a date range array for archive queries.
	 */
	public static function hebrew_date_query( $year, $month = null, $day = null ) {
		// Start with year query
		if ( ! $month && ! $day ) {
			$after  = new JT_Hebrew_Date();
			$before = new JT_Hebrew_Date();
			$after->set_hebrew_date( $year, 1, 1 );
			$before->set_hebrew_date( $year, 13, 29 );
			$before->set_time( 23, 59, 59 );
			return array(
				'before' => $before->to_datetime()->format( DATE_W3C ),
				'after'  => $after->to_datetime()->format( DATE_W3C ),
			);
		}
		if ( ! $day ) {
			$before = new JT_Hebrew_Date();
			$after  = new JT_Hebrew_Date();
			$after->set_hebrew_date( $year, $month, 1 );
			$before->set_hebrew_date( $year, $month, cal_days_in_month( CAL_JEWISH, $month, $year ) );
			$before->set_time( 23, 59, 59 );
			return array(
				'before' => $before->to_datetime()->format( DATE_W3C ),
				'after'  => $after->to_datetime()->format( DATE_W3C ),
			);

		}

		$before = new JT_Hebrew_Date();
		$after  = new JT_Hebrew_Date();
		$after->set_hebrew_date( $year, $month, $day );
		$before->set_hebrew_date( $year, $month, $day );
		$before->set_time( 23, 59, 59 );
		return array(
			'before' => $before->to_datetime()->format( DATE_W3C ),
			'after'  => $after->to_datetime()->format( DATE_W3C ),
		);
	}

	public static function hebrew_date( $query ) {
		// check if the user is requesting an admin page
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// If this is a date archive
		if ( ! empty( $query->get( 'jthdate' ) ) ) {
			if ( is_date() ) {
				$query->set(
					'date_query',
					array(
						self::hebrew_date_query( $query->get( 'year' ), $query->get( 'monthnum' ), $query->get( 'day' ) ),
					)
				);
				$query->set( 'hyear', $query->get( 'year' ) );
				$query->set( 'year', '' );
				$query->set( 'hmonthnum', $query->get( 'monthnum' ) );
				$query->set( 'hday', $query->get( 'day' ) );
				$query->set( 'day', '' );
			}
		}
		return $query;
	}

	public static function is_hebrew_date_archive() {
		return ( is_date() && is_numeric( get_query_var( 'jthdate' ) ) );
	}

	public static function archive_title( $title ) {
		if ( self::is_hebrew_date_archive() ) {
			$hdate = new JT_Hebrew_Date();
			if ( is_year() ) {
				$title  = get_query_var( 'hyear' );
				$prefix = _x( 'Hebrew Year:', 'date archive title prefix', 'jewish-time' );
			}
			if ( is_month() ) {
				$title  = $hdate->get_month_name( get_query_var( 'hmonthnum' ), get_query_var( 'hyear' ) ) . ' ' . get_query_var( 'hyear' );
				$prefix = _x( 'Hebrew Month:', 'date archive title prefix', 'jewish-time' );
			}

			if ( is_day() ) {
				$hdate->set_hebrew_date( get_query_var( 'hyear' ), get_query_var( 'hmonthnum' ), get_query_var( 'hday' ) );
				$title  = $hdate->get_the_date();
				$prefix = _x( 'Hebrew Day:', 'date archive title prefix', 'jewish-time' );
			}
			/**
			 * Filters the archive title prefix.
			 *
			 * @since 5.5.0
			 *
			 * @param string $prefix Archive title prefix.
			 */
			$prefix = apply_filters( 'get_the_archive_title_prefix', $prefix );
			if ( $prefix ) {
				$title = sprintf(
				 /* translators: 1: Title prefix. 2: Title. */
					_x( '%1$s %2$s', 'archive title', 'default' ),
					$prefix,
					'<span>' . $title . '</span>'
				);
			}
		}
		return $title;
	}
}


