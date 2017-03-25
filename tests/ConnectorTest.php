<?php

namespace React\Tests\SocketClient;

use React\SocketClient\Connector;
use React\Promise\Promise;

class ConnectorTest extends TestCase
{
    public function testConnectorUsesTcpAsDefaultScheme()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $tcp = $this->getMockBuilder('React\SocketClient\ConnectorInterface')->getMock();
        $tcp->expects($this->once())->method('connect')->with('127.0.0.1:80');

        $connector = new Connector($loop, array(
            'tcp' => $tcp
        ));

        $connector->connect('127.0.0.1:80');
    }

    public function testConnectorPassedThroughHostnameIfDnsIsDisabled()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $tcp = $this->getMockBuilder('React\SocketClient\ConnectorInterface')->getMock();
        $tcp->expects($this->once())->method('connect')->with('tcp://google.com:80');

        $connector = new Connector($loop, array(
            'tcp' => $tcp,
            'dns' => false
        ));

        $connector->connect('tcp://google.com:80');
    }

    public function testConnectorWithUnknownSchemeAlwaysFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = new Connector($loop);

        $promise = $connector->connect('unknown://google.com:80');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testConnectorWithDisabledTcpDefaultSchemeAlwaysFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = new Connector($loop, array(
            'tcp' => false
        ));

        $promise = $connector->connect('google.com:80');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testConnectorWithDisabledTcpSchemeAlwaysFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = new Connector($loop, array(
            'tcp' => false
        ));

        $promise = $connector->connect('tcp://google.com:80');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testConnectorWithDisabledTlsSchemeAlwaysFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = new Connector($loop, array(
            'tls' => false
        ));

        $promise = $connector->connect('tls://google.com:443');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testConnectorWithDisabledUnixSchemeAlwaysFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = new Connector($loop, array(
            'unix' => false
        ));

        $promise = $connector->connect('unix://demo.sock');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testConnectorUsesGivenResolverInstance()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $promise = new Promise(function () { });
        $resolver = $this->getMockBuilder('React\Dns\Resolver\Resolver')->disableOriginalConstructor()->getMock();
        $resolver->expects($this->once())->method('resolve')->with('google.com')->willReturn($promise);

        $connector = new Connector($loop, array(
            'dns' => $resolver
        ));

        $connector->connect('google.com:80');
    }

    public function testConnectorUsesResolvedHostnameIfDnsIsUsed()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $promise = new Promise(function ($resolve) { $resolve('127.0.0.1'); });
        $resolver = $this->getMockBuilder('React\Dns\Resolver\Resolver')->disableOriginalConstructor()->getMock();
        $resolver->expects($this->once())->method('resolve')->with('google.com')->willReturn($promise);

        $tcp = $this->getMockBuilder('React\SocketClient\ConnectorInterface')->getMock();
        $tcp->expects($this->once())->method('connect')->with('tcp://127.0.0.1:80?hostname=google.com');

        $connector = new Connector($loop, array(
            'tcp' => $tcp,
            'dns' => $resolver
        ));

        $connector->connect('tcp://google.com:80');
    }
}
