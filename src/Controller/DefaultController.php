<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
     /**
      * @Route("/", methods={"GET","HEAD"}, name="home")
      */
    public function index()
    {
        $message = 'Nothing happens';

        $parseFile = Yaml::parseFile('../data/example-fixtures.yaml');
        $fileSystem = $this->initFile();
        $this->buildFile();

        //level 1
        $subkeys = [];
        foreach ($parseFile as $key => $value) {
            $key = $this->escape($key);
            foreach ($value as $subkey => $subvalue) {
                array_push($subkeys, $subkey);
                $subkey = $this->escape($subkey);
                $fileSystem->appendToFile('../export/viz.dot', $key.'--'.$subkey.PHP_EOL);
            }
        }

        //level 2
        foreach ($parseFile as $item) {
            foreach ($subkeys as $key) {
                if(array_key_exists($key, $item)) {
                    $this->getKeys($item[$key]);
                }
            }
        }

        $fileSystem = $this->closeFile($fileSystem);
        return new JsonResponse($message);
    }

    /**
     * @return Filesystem
     */
    private function initFile()
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile('../export/viz.dot', '');
        $fileSystem->appendToFile('../export/viz.dot', 'graph {'.PHP_EOL);
        $fileSystem->mkdir('../export');
        $fileSystem->touch('../export/example.dot');

        return $fileSystem;
    }

    private function buildFile()
    {

    }

    /**
     * @param Filesystem $fileSystem
     * @return Filesystem
     */
    private function closeFile($fileSystem)
    {
        $fileSystem->appendToFile('../export/viz.dot', '}');
        return $fileSystem;
    }

    /**
     * @param $string
     * @return null|string|string[]
     */
    private function escape($string)
    {
        return $result = preg_replace("#[\\\-]#", "", "$string");
    }

    /**
     * @param $array
     */
    private function getKeys($array)
    {
        foreach ($array as $key => $value) {
            dump($key);
        }
    }


    private function addDotNodeIntoFile($fileSystem, $origin, $target)
    {
        $fileSystem->appendToFile('../export/viz.dot', $origin.'--'.$target.PHP_EOL);
        return $fileSystem;
    }

}
