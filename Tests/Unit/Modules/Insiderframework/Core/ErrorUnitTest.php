<?php
use PHPUnit\Framework\TestCase;
use \Modules\Insiderframework\Core\Error;

final class ErrorUnitTest extends TestCase
{
  public function testShouldCallPrimaryErrorAndReturnJsonWithHttpResponseCode500(): void
  {
    $errorMsg = "Should not complete the test";
    $expectedHttpResponseCode = 500;

    try{
      Error::primaryError(
        $errorMsg
      );
    } catch(\Exception $err) {
      $result = $err->getMessage();
    }

    $exceptedReturn['error'] = $errorMsg;
    $this->assertEquals(json_encode($exceptedReturn), $result);
    $this->assertEquals($expectedHttpResponseCode, http_response_code());
  }

  public function testShouldCallPrimaryErrorAndReturnStringWithHttpResponseCode400(): void
  {
    $exceptedReturn = "Should not complete the test";
    $expectedHttpResponseCode = 400;

    try{
      Error::primaryError(
        $exceptedReturn,
        $expectedHttpResponseCode,
        "STRING"
      );
    } catch(\Exception $err){
      $result = $err->getMessage();
    }

    $this->assertEquals($exceptedReturn, $result);    
    $this->assertEquals($expectedHttpResponseCode, http_response_code());
  }
}