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
        $parseFile = Yaml::parseFile('../data/example.yaml');

        $fileSystem = $this->initFile();
        $this->buildFile();

        //change parse
        foreach ($parseFile as $item) {

            foreach ($item as $subitem) {

                $fileSystem->appendToFile('../export/viz.dot', array_keys($item)[0].'--'.array_keys($subitem)[0].PHP_EOL);
                $fileSystem->appendToFile('../export/viz.dot', array_keys($item)[0].'--'.array_keys($subitem)[1].PHP_EOL);
                $fileSystem->appendToFile('../export/viz.dot', array_keys($item)[0].'--'.array_keys($subitem)[2].PHP_EOL);
            }
        }

        dump($parseFile);die;

        $this->closeFile($fileSystem);

        return new JsonResponse($message);
    }

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

    private function closeFile($fileSystem)
    {
        $result = $fileSystem->appendToFile('../export/viz.dot', '}');
        return $result;
    }


}
