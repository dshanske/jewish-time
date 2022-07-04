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
}

