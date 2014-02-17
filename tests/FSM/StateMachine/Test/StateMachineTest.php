<?php
namespace FSM\StateMachine\Test;

use FSM\StateMachine\StateMachine;
use FSM\StateMachine\State\State;
use FSM\StateMachine\State\StateInterface;
use FSM\StateMachine\Transition\Transition;

class StateMachineTest extends \PHPUnit_Framework_TestCase
{
    private $stateMachineMock;
    private $stateMachine;

    public function setUp()
    {
        $stateMachine = $this->getMockBuilder('FSM\StateMachine\StateMachine')
            ->setMethods(array('configureMachine'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->stateMachineMock = $stateMachine;
        $this->stateMachine = new MockStateMachine('Machine Name');
    }

    public function testConfigure()
    {
        $this->stateMachineMock->expects($this->once())
            ->method('configureMachine');

        $this->stateMachineMock->__construct('name');
    }

    public function testGetName()
    {
        $this->stateMachineMock->__construct('Machine Name');

        $this->assertSame('Machine Name', $this->stateMachineMock->getName());
    }

    public function testInitialState()
    {
        $this->assertSame('created', $this->stateMachine->getCurrentState()->getName());
    }

    public function testAddGetTransitions()
    {
        // Count transitions
        $transitions = $this->stateMachine->getTransitions();
        $this->assertTrue(count($transitions) == 2);

        // Check the available transitions
        $transitions = $this->stateMachine->getAvailableTransitions();
        $transition = array_shift($transitions);

        $this->assertSame('created', $transition->getInitialState()->getName());
        $this->assertSame('imported', $transition->getTransitionTo()->getName());

        // Check available states
        $this->assertTrue(count($this->stateMachine->getStates()) == 3);
    }

    public function testTriggerTransition()
    {
        $this->assertSame('created', $this->stateMachine->getCurrentState()->getName());
        $this->stateMachine->trigger('import');
        $this->assertSame('imported', $this->stateMachine->getCurrentState()->getName());
    }

    public function testDeepTriggerTransition()
    {
        $this->assertSame('created', $this->stateMachine->getCurrentState()->getName());
        $this->stateMachine->trigger('deepImport', true);
        $this->assertSame('live', $this->stateMachine->getCurrentState()->getName());
    }
}

class MockStateMachine extends StateMachine
{
    protected function configureMachine()
    {
        $created = new State('created', StateInterface::TYPE_INITIAL);
        $imported = new State('imported');
        $live = new State('live', StateInterface::TYPE_FINAL);

        $importTransition = new Transition($created, $imported);
        $liveTransition = new Transition($imported, $live);

        $this->addTransition($importTransition);
        $this->addTransition($liveTransition);

        $this->addTrigger('import', array($importTransition));
        $this->addTrigger('deepImport', array($importTransition, $liveTransition));

        $this->setCurrentState('created');
    }
}