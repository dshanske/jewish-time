<?php

class HebrewDateTest extends WP_UnitTestCase {
	public function test_convert_hebrew_date_to_gregorian_date() {
		$hdate = new JT_Hebrew_Date();
		$hdate->set_hebrew_date( 5782, 11, 19 );
		$datetime = $hdate->to_datetime();
		$this->assertEquals( 7, $datetime->format( 'n' ) );
		$this->assertEquals( 18, $datetime->format( 'j' ) );
		$this->assertEquals( 2022, $datetime->format( 'Y' ) );
	}
	public function test_hebrew_date_datetime() {
		$datetime    = new DateTime( '2022-07-18' );
		$hdate       = new JT_Hebrew_Date( $datetime );
		$newdatetime = $hdate->to_datetime();
		$this->assertEquals( $datetime->format( DATE_W3C ), $newdatetime->format( DATE_W3C ) );
	}
	public function test_gregorian_to_hebrew_date() {
		$hdate = new JT_Hebrew_Date( new DateTime( '2022-07-18' ) );
		$array = (array) $hdate;
		$this->assertEquals( 5782, $hdate->year, wp_json_encode( $array ) );
		$this->assertEquals( 11, $hdate->month );
		$this->assertEquals( 19, $hdate->day );
	}

	public function test_get_the_month_name_in_english() {
		$hdate = new JT_Hebrew_Date( new DateTime( 'April 2, 2017' ) );
		$this->assertEquals( 'Nisan', $hdate->get_month_name() );
	}

	public function test_get_the_day_of_the_week() {
		$hdate = new JT_Hebrew_Date( new DateTime( 'April 2, 2017' ) );
		$this->assertEquals( 'Yom Rishon', $hdate->day_of_week() );
	}

	public function test_if_the_date_is_part_of_a_leap_year() {
		$hdate = new JT_Hebrew_Date( new DateTime( '2017-01-01' ) );
		$this->assertFalse( $hdate->is_leap_year() );
		$hdate = new JT_Hebrew_Date( new DateTime( '2019-01-01' ) );
		$this->assertTrue( $hdate->is_leap_year() );
	}

	public function test_adar_as_the_month_if_it_is_not_a_leap_year() {
		$hdate = new JT_Hebrew_Date( new DateTime( 'February 27, 2017' ) );
		$this->assertEquals( 'Adar', $hdate->get_month_name() );
	}

	public function test_adar_1_and_adar_2_as_the_months_if_it_is_a_leap_year() {
		$hdate = new JT_Hebrew_Date( new DateTime( 'February 27, 2019' ) );
		$this->assertTrue( $hdate->is_leap_year() );
		$this->assertEquals( 6, $hdate->month );
		$this->assertEquals( 'Adar I', $hdate->get_month_name() );
		$hdate = new JT_Hebrew_Date( new DateTime( 'March 27, 2019' ) );
		$this->assertEquals( 7, $hdate->month );
		$this->assertTrue( $hdate->is_leap_year() );
		$this->assertEquals( 'Adar II', $hdate->get_month_name() );
	}
}

