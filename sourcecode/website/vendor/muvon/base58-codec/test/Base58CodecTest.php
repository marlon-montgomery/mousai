<?php
use Muvon\KISS\Base58Codec;
use PHPUnit\Framework\TestCase;

class Base58CodecTest extends TestCase {
  
  public function testCheckEncode() {
    $this->assertEquals(
      '3JuEUFpEgcrr2h3RkoyUwQo4SVvo3jQpFBS672mw1ffRh39y2gC7sM',
      Base58Codec::checkEncode('cd1400c7e9cd6e20a157750ce9172f6a8775112648e56e1aafc148496a513ed115f0af')
    );
  }

  public function testCheckDecode() {
    $this->assertEquals(
      'cd1400c7e9cd6e20a157750ce9172f6a8775112648e56e1aafc148496a513ed115f0af',
      Base58Codec::checkDecode('3JuEUFpEgcrr2h3RkoyUwQo4SVvo3jQpFBS672mw1ffRh39y2gC7sM')
    );
  }

  public function testEncode() {
    $this->assertEquals(
      '2z41adftbuTvXzkxxhhg8cFvZLKMzFv7dHgLFj8EeLEF8KvE9yRGqZjJDwyqPPZ7KKapu9rTPrbMeJFeDtVub4AZ',
      Base58Codec::encode('c7e9cd6e20a157750ce9172f6a8775112648e56e1aafc148496a513ed115f0af')
    );
  }

  public function testDecode() {
    $this->assertEquals(
      'c7e9cd6e20a157750ce9172f6a8775112648e56e1aafc148496a513ed115f0af',
      Base58Codec::decode('2z41adftbuTvXzkxxhhg8cFvZLKMzFv7dHgLFj8EeLEF8KvE9yRGqZjJDwyqPPZ7KKapu9rTPrbMeJFeDtVub4AZ')
    );
  }
}