<?php
/**
 * Hebrew Date Class
 *
 */
class JT_Hebrew_Date {

	/* Hebrew Year.
	 */
	protected $year;

	/* Hebrew Month.
	 */
	protected $month;

	/* Hebrew Month Name in English.
	 */
	protected $month_name;

	/* Hebrew Month Name.
	 */
	protected $hebrew_month_name;

	/* Hebrew Day.
	 */
	protected $day;

	/* Day of Week.
	 */
	protected $day_of_week;


	/* Days in Month.
	 */
	protected $days_in_month;

	/* Hours.
	 */
	protected $hour = 0;

	/* Minutes.
	 */
	protected $minute = 0;

	/* Seconds.
	 */
	protected $second = 0;

	/* Milliseconds.
	 */
	protected $millisecond = 0;

	/* Timezone.
	 *
	 */
	protected $timezone;

	/*
	 * Constructor.
	 *
	 * @param DateTime|DateTimeImmutable $datetime Date Time Object to convert to Hebrew Time.
	 */

	public function __construct( $datetime = null ) {
		if ( $datetime instanceof DateTimeInterface ) {
			$this->create_from_datetime( $datetime );
		}
		if ( ! $this->timezone ) {
			$this->timezone = wp_timezone();
		}
	}

	public function set_hebrew_date( $year, $month, $day ) {
		$this->year        = (int) $year;
		$this->month       = (int) $month;
		$this->day         = (int) $day;
		$jd                = cal_to_jd( CAL_JEWISH, $year, $month, $day );
		$this->day_of_week = (int) jddayofweek( $jd ) + 1;
		$month_names       = cal_info( CAL_JEWISH )['months'];
		if ( $this->is_leap_year() ) {
			$month_names[7] = 'Adar';
		}
		$month_names             = apply_filters( 'jewish_time_month_names', $month_names );
		$this->month_name        = $month_names[ $this->month ];
		$this->hebrew_month_name = $this->hebrew_month_name( $this->month );
		$this->days_in_month     = $this->cal_days_in_month();
		return true;
	}

	public function set_time( $hour, $minute, $second = 0, $millisecond = 0 ) {
		$this->hour        = $hour;
		$this->minute      = $minute;
		$this->second      = $second;
		$this->millisecond = $millisecond;
	}

	public function to_datetime() {
		$jd        = jewishtojd( $this->year, $this->month, $this->day );
		$timestamp = jdtounix( $jd );
		$datetime  = new DateTime();
		$datetime->setTimeZone( $this->timezone );
		$datetime->setTimestamp( $timestamp );
		$datetime->setTime( $this->hour, $this->minute, $this->second, $this->millisecond );
		return DateTimeImmutable::createFromMutable( $datetime );
	}

	protected function create_from_datetime( $datetime ) {
		if ( ! $datetime instanceof DateTimeInterface ) {
			return false;
		}

		$timestamp = $datetime->getTimestamp();
		$jd        = unixtojd( $timestamp );
		$cal       = cal_from_jd( $jd, CAL_JEWISH );
		$this->set_hebrew_date( $cal['year'], $cal['month'], $cal['day'] );
		$this->hour        = (int) $datetime->format( 'G' );
		$this->minute      = (int) $datetime->format( 'i' );
		$this->second      = (int) $datetime->format( 's' );
		$this->day_of_week = (int) $datetime->format( 'w' ) + 1;
		$this->millisecond = (int) $datetime->format( 'u' );
		$this->timezone    = $datetime->getTimezone();
		return true;
	}

	public function toArray() {
		return get_object_vars( $this );
	}

	/*
	* Returns whether the year is a leap year
	*
	* @return boolean
	*/
	public function is_leap_year() {
		return ( 1 + ( $this->year * 7 ) ) % 19 < 7 ? true : false;
	}

	/*
	* Wrapper around calendar day in months that returns the number of days in the provided month.
	*
	* @return int Days in Month.
	*/
	public function cal_days_in_month() {
		return cal_days_in_month( CAL_JEWISH, $this->month, $this->year );
	}

	/**
	 * Get the Jewish month name in Hebrew.
	 *
	 * @return string
	 */
	private function hebrew_month_name() {
		$months = array(
			'תשרי',
			'חשון',
			'כסלו',
			'טבת',
			'שבט',
			'אדר א',
			$this->is_leap_year() ? 'אדר ב' : 'אדר',
			'ניסן',
			'אייר',
			'סיון',
			'תמוז',
			'אב',
			'אלול',
		);
		return $months[ $this->month - 1 ];
	}

	public function get_the_date() {
		return sprintf( '%1$s %2$s %3$s', $this->day, $this->month_name, $this->year );
	}

	public function get_the_hebrew_date() {
		return sprintf( '<span dir="rtl" lang="he">%1$s %2$s %3$s</span>', self::number_to_hebrew( $this->day ), $this->hebrew_month_name, self::number_to_hebrew( $this->year ) );
	}

	/**
	 * Split a number into an array of parts which
	 * corresponds with Hebrew gematria values.
	 * Borrowed from Zman Library(see credits).
	 *
	 * @param  string|int $number
	 * @return array
						  */
	private static function number_to_array( $number ) {
		$result = array();
		while ( $number > 0 ) {
			$incr = 100;
			if ( $number == 15 || $number == 16 ) {
				$result[] = 9;
				$result[] = $number - 9;
				break;
			}
			for ( $i = 400; $i > $number; $i -= $incr ) {
				if ( $i == $incr ) {
					  $incr = (int) ( $incr / 10 );
				}
			}
			$result[] = $i;
			$number  -= $i;
		}
		return $result;
	}

	/**
	 * Convert a number to Hebrew.
	 * Borrowed from Zman Library(see credits).
	 *
	 * @param  string|int $number
	 * @return string
	 */
	public static function number_to_hebrew( $number ) {
		$arr     = self::number_to_array( $number );
		$numbers = array(
			1   => "\327\220",
			2   => "\327\221",
			3   => "\327\222",
			4   => "\327\223",
			5   => "\327\224",
			6   => "\327\225",
			7   => "\327\226",
			8   => "\327\227",
			9   => "\327\230",
			10  => "\327\231",
			20  => "\327\233",
			30  => "\327\234",
			40  => "\327\236",
			50  => "\327\240",
			60  => "\327\241",
			70  => "\327\242",
			80  => "\327\244",
			90  => "\327\246",
			100 => "\327\247",
			200 => "\327\250",
			300 => "\327\251",
			400 => "\327\252",
		);
		$digits  = count( $arr );
		if ( $digits == 1 ) {
			 $result = $numbers[ $arr[0] ] . "\327\263"; // geresh
		} else {
			$result = '';
			for ( $i = 0; $i < $digits; $i++ ) {
				if ( ( $i + 1 ) == $digits ) {
					$result .= "\327\264"; // gershayim
				}
				$result .= $numbers[ $arr[ $i ] ];
			}
		}
		return $result;
	}


}

