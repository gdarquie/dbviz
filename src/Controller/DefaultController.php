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
        $value = Yaml::parseFile('../data/example.yaml');

        $fileSystem = new Filesystem();
        $fileSystem->mkdir('../export');
        $fileSystem->touch('../export/file.txt');
        $fileSystem->dumpFile('../export/file.txt', 'Hello World');

        return new JsonResponse($message);
    }

}
