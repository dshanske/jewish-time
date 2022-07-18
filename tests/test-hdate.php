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
		$datetime = new DateTime( '2022-07-18' );
		$hdate = new JT_Hebrew_Date( $datetime );
		$newdatetime = $hdate->to_datetime();
		$this->assertEquals( $datetime->format( DATE_W3C ), $newdatetime->format( DATE_W3C ) );
	}
}

