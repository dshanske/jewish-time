<?php
/**
 * Hebrew Date Class
 *
 */
class JT_Hebrew_Date {

	/* Hebrew Year.
	 */
	public $year;

	/* Hebrew Month.
	 */
	public $month;

	/* Hebrew Month Name in English.
	 */
	public $month_name;

	/* Hebrew Month Name.
	 */
	public $hebrew_month_name;

	/* Hebrew Day.
	 */
	public $day;

	/* Day of Week.
	 */
	public $day_of_week;


	/* Days in Month.
	 */
	public $days_in_month;

	/* Hours.
	 */
	public $hour = 0;

	/* Minutes.
	 */
	public $minute = 0;

	/* Seconds.
	 */
	public $second = 0;

	/* Milliseconds.
	 */
	public $millisecond = 0;

	/* Timezone.
	 *
	 */
	public $timezone;

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
		$this->year              = (int) $year;
		$this->month             = (int) $month;
		$this->day               = (int) $day;
		$jd                      = cal_to_jd( CAL_JEWISH, $year, $month, $day );
		$this->day_of_week       = (int) jddayofweek( $jd );
		$this->month_name        = $this->get_month_name();
		$this->hebrew_month_name = $this->hebrew_month_name( $this->month );
		$this->days_in_month     = $this->cal_days_in_month();
		return true;
	}

	public function get_month_name( $month = null, $year = null ) {
		if ( ! $month ) {
			$month = $this->month;
		}
		if ( ! $year  ) {
			$year = $this->year;
		}

		$month_names = cal_info( CAL_JEWISH )['months'];
		if ( ! $this->is_leap_year( $year ) ) {
			$month_names[7] = 'Adar';
		}
		$month_names = apply_filters( 'jewish_time_month_names', $month_names );
		return $month_names[ $month ];
	}

	public function set_time( $hour, $minute, $second = 0, $millisecond = 0 ) {
		$this->hour        = $hour;
		$this->minute      = $minute;
		$this->second      = $second;
		$this->millisecond = $millisecond;
	}

	public function to_datetime() {
		$jd        = jewishtojd( $this->month, $this->day, $this->year );
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
		$this->day_of_week = (int) $cal['dow'];
		$this->millisecond = (int) $datetime->format( 'u' );
		$this->timezone    = $datetime->getTimezone();
		return true;
	}

	/*
	* Returns whether the year is a leap year
	*
	* @return boolean
	*/
	public function is_leap_year( $year = null ) {
		if ( ! $year ) {
			$year = $this->year;
		}
		if ( 0 === ( $year % 19 ) || 3 === ( $year % 19 ) || 6 === ( $year % 19 ) ||
			   8 === ( $year % 19 ) || 11 === ( $year % 19 ) || 14 === ( $year % 19 ) ||
					 17 === ( $year % 19 ) ) {
					return true;
		}
		return false;
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

	/**
	 *
	 * @return string
	 */
	private function hebrew_day_of_week() {
		$days = array(
			'יום ראשון',
			'יום שני',
			'יום שלישי',
			'יום רבעי',
			'יום חמישי',
			'יום ששי',
			'יום השׁביעי',
		);
		return $days[ $this->day_of_week ];
	}

	/**
	 *
	 * @return string
	 */
	private function hebrew_day_of_week_abbr() {
		$days = array(
			'יום א׳',
			'יום ב׳',
			'יום ג׳',
			'יום ד׳',
			'יום ה׳',
			'יום ו׳',
			'יום ש׳',
		);
		return $days[ $this->day_of_week ];
	}

	/**
	 *
	 * @return string
	 */
	private function day_of_week() {
		$days = array(
			'Yom Rishon',
			'Yom Sheni',
			'Yom Shlishi',
			"Yom Revi'i",
			'Yom Chamishi',
			'Yom Shishi',
			"Yom Shevi'i",
		);
		return $days[ $this->day_of_week ];
	}

	public function get_the_date( $format = 'j F Y' ) {
		$datetime      = $this->to_datetime();
		$format_length = strlen( $format );
		$output        = '';
		for ( $i = 0; $i < $format_length; $i ++ ) {
			switch ( $format[ $i ] ) {
				// A full numeric representation of a year, at least 4 digits
				case 'Y':
					$output .= $this->year;
					break;
				// A full textual representation of a month, such as January or March
				case 'F':
					$output .= $this->month_name;
					break;
				// Numeric representation of a month, with leading zeros
				case 'm':
					$output .= str_pad( $this->month, 2, '0', STR_PAD_LEFT );
					break;
				// Numeric representation of a month, without leading zeros
				case 'n':
					$output .= $this->month;
					break;
				// A short textual representation of a month, three letters
				case 'M':
					$output .= $this->month_name;
					break;
				// Number of days in the given month
				case 't':
					$output .= $this->days_in_month;
					break;
				// Whether it's a leap year.
				case 'L':
					$output .= $this->is_leap_year() ? 1 : 0;
					break;

				// Day of the month, 2 digits with leading zeros
				case 'd':
					$output .= str_pad( $this->day, 2, '0', STR_PAD_LEFT );
					break;
				// A textual representation of a day, three letters
				case 'D':
					$output .= $this->hebrew_day_of_week_abbr();
					break;
				// A full textual representation of a day
				case 'l':
					$output .= $this->day_of_week();
					break;
				// Day of the month without leading zeros
				case 'j':
					$output .= $this->day;
					break;
				default:
					$output .= $datetime->format( $format[ $i ] );
					break;
			}
		}
		return $output;
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
			if ( 15 === $number || 16 === $number ) {
				$result[] = 9;
				$result[] = $number - 9;
				break;
			}
			for ( $i = 400; $i > $number; $i -= $incr ) {
				if ( $i === $incr ) {
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
		if ( 1 === $digits ) {
			 $result = $numbers[ $arr[0] ] . "\327\263"; // geresh
		} else {
			$result = '';
			for ( $i = 0; $i < $digits; $i++ ) {
				if ( ( $i + 1 ) === $digits ) {
					$result .= "\327\264"; // gershayim
				}
				$result .= $numbers[ $arr[ $i ] ];
			}
		}
		return $result;
	}


}

