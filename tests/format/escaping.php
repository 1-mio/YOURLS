<?php

/**
 * Escaping formatting functions.
 * Note: tests about escaping and sanitizing URLs are in urls.php
 *
 * @group formatting
 * @since 0.1
 */
class Format_Esc extends PHPUnit_Framework_TestCase {
    
    protected $ydb_backup;
    
    protected function setUp() {
        global $ydb;
        $this->ydb_backup = $ydb;
    }

    protected function tearDown() {
        global $ydb;
        $ydb = $this->ydb_backup;
    }

    /**
     * Attributes and how they should be escaped
     */
    function html_attributes() {
        return array(
            array(
                '"double quotes"',
                '&quot;double quotes&quot;',
            ),
            array(
                "'single quotes'",
                '&#039;single quotes&#039;',
            ),
            array(
                "'mixed' " . '"quotes"',
                '&#039;mixed&#039; &quot;quotes&quot;',
            ),
            array(
                'foo & bar &baz; &apos;',
                'foo &amp; bar &amp;baz; &apos;',
            ),
        );
    }


    /**
	 * Attribute escaping
	 *
	 * @dataProvider html_attributes
	 * @since 0.1
	 */	
	function test_esc_attr( $attr, $escaped ) {
		$this->assertSame( $escaped, yourls_esc_attr( $attr ) );
	}
    
    /**
	 * Attribute escaping -- escaping twice shouldn't change
	 *
	 * @dataProvider html_attributes
	 * @since 0.1
	 */	
	function test_esc_attr_twice( $attr, $escaped ) {
		$this->assertSame( $escaped, yourls_esc_attr( yourls_esc_attr( $attr ) ) );
	}
    
    /**
     * HTML string and how they should be escaped
     */
    function html_strings() {
        return array(
            // Simple string
            array( 
                'The quick brown fox.',
                'The quick brown fox.',
            ),
            // URL with &
            array(
                'https://127.0.0.1/admin/admin-ajax.php?id=y1120844669&action=edit&keyword=1a&nonce=bf3115ac3a',
                'https://127.0.0.1/admin/admin-ajax.php?id=y1120844669&amp;action=edit&amp;keyword=1a&amp;nonce=bf3115ac3a',
            ),
            // More ampersands
            array(
                'H&M and Dungeons & Dragons',
                'H&amp;M and Dungeons &amp; Dragons',
            ),
            // Simple quotes
            array(
                "SELECT stuff FROM table WHERE blah IN ('omg', 'wtf') AND foo = 1",
                'SELECT stuff FROM table WHERE blah IN (&#039;omg&#039;, &#039;wtf&#039;) AND foo = 1',
            ),
            // Double quotes
            array(
                'I am "special"',
                'I am &quot;special&quot;',
            ),
            // Greater and less than
            array(
                'this > that < that <randomhtml />',
                'this &gt; that &lt; that &lt;randomhtml /&gt;',
            ),
            // Ignore actual entities
            array(
                '&#038; &#x00A3; &#x22; &amp;',
                '&amp; &#xA3; &quot; &amp;',
            ),
            // Empty string
            array(
                '',
                '',
            ),
        );
    }
	
	/**
	 * HTML escaping
	 *
     * @dataProvider html_strings
	 * @since 0.1
	 */	
	function test_esc_html( $html, $escaped ) {
		$this->assertSame( $escaped, yourls_esc_html( $html ) );
	}
    
    /**
     * String to escape and what they should look like once escaped
     */
    public function strings_to_escape() {
        return array(
           array( "I'm rock n' rollin'", "I\'m rock n\' rollin\'" ),
           array( 'I am "nice"', 'I am \"nice\"' ),
           array( 'Back\Slash', 'BackSlash' ),
           array( "NULL\0NULL", 'NULL\0NULL' ), // notice the quote change
        );
    }
    
    /**
     * Escape strings
     *
     * @since 0.1
     * @dataProvider strings_to_escape
     */
    public function test_yourls_escape_string( $string, $escaped ) {
        $this->assertSame( yourls_escape( $string ), $escaped );
    }

    /**
     * String to addslash and what they should look like once addslashed
     */
    public function strings_to_addslash() {
        return array(
           array( "I'm rock n' rollin'", "I\'m rock n\' rollin\'" ),
           array( 'I am "nice"', 'I am \"nice\"' ),
           array( 'Back\Slash', 'Back\\\Slash' ),
           array( "NULL\0NULL", 'NULL\0NULL' ), // notice the quote change
        );
    }
    
    /**
     * Escape strings when no DB connection exists
     *
     * @since 0.1
     * @dataProvider strings_to_addslash
     */
    public function test_yourls_escape_string_with_no_DB( $string, $escaped ) {
        global $ydb;
        $ydb = "not a DB instance";
        
        $this->assertSame( yourls_escape( $string ), $escaped );
    }

    /**
     * Escape arrays
     *
     * @since 0.1
     */
    public function test_yourls_escape_array() {
        
        global $ydb;
        
        $arrays = $this->strings_to_escape();
        $array_str = array();
        $array_esc = array();
        
        foreach( $arrays as $array ) {
            $array_str[] = $array[0];
            $array_esc[] = $array[1];
        }
        $array_str = array( $array_str );
        $array_esc = array( $array_esc );        
        
        $this->assertSame( yourls_escape( $array_str ), $array_esc );
    }
    
    /**
     * List of URLs and how they should be escaped
     */
    function list_of_URLs() {
        return array(
            array(
                'http://example.com/?this=that&that=this',
                'http://example.com/?this=that&#038;that=this',
            ),
            array(
                'http://example.com/?this=that&that="this"',
                'http://example.com/?this=that&#038;that=this',
            ),
            array(
                "http://example.com/?this=that&that='this'",
                'http://example.com/?this=that&#038;that=&#039;this&#039;',
            ),
            array(
                "http://example.com/?this=that&that=<this>",
                'http://example.com/?this=that&#038;that=this',
            ),
        );
    }
    
    /**
     * Escape URLs for display
     *
     * @since 0.1
     * @group url
     * @dataProvider list_of_URLs
     */
    function test_valid_urls( $url, $escaped ) {
        $this->assertEquals( $escaped, yourls_esc_url( $url ) );
    }

}
