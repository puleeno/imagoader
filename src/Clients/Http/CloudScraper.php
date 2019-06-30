<?php
namespace Puleeno\Goader\Clients\Http;

use Puleeno\Goader\Interfaces\Http\ClientInterface;
use Puleeno\Goader\Environment;
use Puleeno\Goader\Clients\Http\Response;

class CloudScraper implements ClientInterface
{
    protected $binFile;
    protected $nodeBinary;
    protected $options = [];

    public function __construct($options = [])
    {
        $this->bindFile = $this->getBinary();
        $this->nodeBinary = $this->getNodeBinary();
    }

    public function getNodeBinary()
    {
        if (empty($node = getenv('GLOADER_NODE_BINARY'))) {
            return 'node';
        }
        return $node;
    }

    private function getBinary()
    {
        return sprintf(
            '%s/goader.js',
            Environment::getGoaderDir()
        );
    }

    protected function buildCommand($commandArgs)
    {
        $command = '';

        foreach ($commandArgs as $key => $val) {
            if (is_string($key)) {
                $command .= sprintf(' --%s=%s', $key, $val);
            } else {
                $command .= ' ' . $val;
            }
        }
        return sprintf(
            '%s "%s" %s',
            $this->getNodeBinary(),
            $this->getBinary(),
            ltrim($command)
        );
    }

    public function executeCommand($command)
    {
        exec($command, $output);
        return implode("\n", $output);
    }

    public function request($method, $uri = '', $options = [])
    {
        $commandArgs = array();

        $commandArgs['method'] = $method;
        $commandArgs[] = $uri;

        $command = $this->buildCommand($commandArgs);
        $body = $this->executeCommand($command);

        $res = new Response($body, 200);
        return $res;
    }

    public function setUserAgent($agent)
    {
    }
}
