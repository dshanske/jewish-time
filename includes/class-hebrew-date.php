<?php
/**
 * Hebrew Date Class
 *
 */
class JT_Hebrew_Date {

	/* Date Time Object
	 */
	protected $datetime;

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


	/* Days in Month.
	 */
	protected $days_in_month;

	/*
	 * Constructor.
	 *
	 * @param DateTime $datetime Date Time Object to convert to Hebrew Time.
	 */

	public function __construct( $datetime ) {
		if ( $datetime instanceof DateTime ) {
			$this->datetime = DateTimeImmutable::createFromMutable( $datetime );
		} else {
			$this->datetime = $datetime;
		}

		$timestamp   = $datetime->getTimestamp();
		$jd          = unixtojd( $timestamp );
		$cal         = cal_from_jd( $jd, CAL_JEWISH );
		$this->month = $cal['month'];
		$month_names = cal_info( CAL_JEWISH )['months'];
		if ( $this->is_leap_year() ) {
			$month_names[7] = 'Adar';
		}
		$month_names             = apply_filters( 'jewish_time_month_names', $month_names );
		$this->month_name        = $month_names[ $this->month ];
		$this->hebrew_month_name = $this->hebrew_month_name( $this->month );
		$this->day               = $cal['day'];
		$this->year              = $cal['year'];
		$this->days_in_month     = $this->cal_days_in_month();
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


}

