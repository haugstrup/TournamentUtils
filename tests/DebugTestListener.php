<?php
use PHPUnit\Framework\TestListener;
class DebugTestListener implements TestListener {
  public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {}
  public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {}
  public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time) {}
  public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {}
  public function startTest(PHPUnit_Framework_Test $test) {}
  public function endTest(PHPUnit_Framework_Test $test, $time) {}
  public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {}
  public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {}
  public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
  {
    if (method_exists($test, 'printDebugInfo')) {
      printf("Test '%s' failed:\n", $test->getName());
      $test->printDebugInfo();
    }
  }
}
