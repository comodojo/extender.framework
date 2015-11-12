<?php

class JobsResultTest extends \PHPUnit_Framework_TestCase {

    private $processes = array(
        array(100,'test',true,1445032806,1445032808,'Success',10,1),
        array(101,'tes2',false,1445032806,1445032808,'Failure',null,2)
    );

    private $struct = array('pid','name','success','start','end','result','id','wid');

    protected function setUp() {
        
        $this->job = new \Comodojo\Extender\Runner\JobsResult($this->processes);
    
    }

    protected function tearDown() {

        unset($this->job);

    }

    public function testResult() {

        $raw = $this->job->get();

        $this->assertSame($this->processes, $raw);

        $normalized = $this->job->get(false);

        $this->assertInternalType('array', $normalized);

        foreach ($normalized as $key => $value) {
        
            foreach ($this->struct as $k => $v) {
                
                $this->assertArrayHasKey($v, $value);

            }
            
        }

    }

}
