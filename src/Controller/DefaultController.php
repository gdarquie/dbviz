<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{

    private $message = 'Nothing happens';

    private $nbLines = 0;

    private $level = 0;

     /**
      * @Route("/", methods={"GET","HEAD"}, name="home")
      */
    public function index()
    {
        $parseFile = Yaml::parseFile('../data/example.yaml');
        $fileSystem = $this->initFile();
        $this->parse($fileSystem, $parseFile);
        $this->closeFile($fileSystem);
        $this->message = $this->rapport();

        return new JsonResponse($this->message);
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
        return $result = preg_replace("#[&\\\-]#", "", "$string");
    }

    /**
     * @param $fileSystem
     * @param $origin
     * @param $target
     * @return mixed
     */
    private function addDotNodeIntoFile($fileSystem, $origin, $target)
    {
        $fileSystem->appendToFile('../export/viz.dot', $origin.'--'.$target.PHP_EOL);
        $this->nbLines++;
    }

    private function parse($fileSystem, $parseFile, $maxLevel = 500)
    {
        $this->level++;
        if($this->level > $maxLevel ) {
            return;
        }

        foreach ($parseFile as $key => $value) {
            if (gettype($value) === 'array')
            {
                $origin = $this->escape($key);
                foreach ($value as $rowkey => $rowvalue) {
                    if (gettype($value) === 'array')
                    {
                        $target = $this->escape($rowkey);
                        $this->parse($fileSystem, $value);



                    }
                    else {
                        $target = $this->escape($rowvalue);
                    }
                    $this->addDotNodeIntoFile($fileSystem, $origin, $target);
                }
            }
        }


        return $this->level;
    }

    /**
     * @return string
     */
    private function rapport() {
        return $this->nbLines.' lignes ont été ajoutées.';
    }

}
