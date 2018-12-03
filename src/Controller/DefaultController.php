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
     * @var string
     */
    private $message = 'Nothing happens';

    /**
     * @var int
     */
    private $nbLines = 0;

    /**
     * @var int
     */
    private $level = 0;

    /**
     * @Route("/", methods={"GET","HEAD"}, name="home")
     */
    public function index()
    {
        $parseFile = Yaml::parseFile('../data/example.yaml');
        $fileSystem = $this->initFile();
        $this->transform($parseFile, $fileSystem);
        $this->closeFile($fileSystem);

        return new JsonResponse($this->rapport());
    }

    /**
     * @return Filesystem
     */
    private function initFile()
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile('../export/viz.dot', '');
        $fileSystem->appendToFile('../export/viz.dot', 'graph {' . PHP_EOL);
        $fileSystem->mkdir('../export');
        $fileSystem->touch('../export/example.dot');

        return $fileSystem;
    }

    /**
     * @param Filesystem $fileSystem
     *
     * @return Filesystem
     */
    private function closeFile($fileSystem)
    {
        $fileSystem->appendToFile('../export/viz.dot', '}');

        return $fileSystem;
    }

    /**
     * @param $string
     *
     * @return null|string|string[]
     */
    private function escape($string)
    {
        return $result = preg_replace("#[;.&\\\-]#", "", "$string");
        // | et space
    }

    /**
     * @param Filesystem $fileSystem
     * @param string     $origin
     * @param string     $target
     */
    private function addDotNodeIntoFile(Filesystem $fileSystem, string $origin, string $target)
    {
        $fileSystem->appendToFile('../export/viz.dot', $origin . '--' . $target . PHP_EOL);
        $this->nbLines++;
    }

    /**
     * @param array $array
     * @param Filesystem $fileSystem
     * @param string $parentKey
     * @param int $maxLevel
     * @return bool|void
     */
    public function transform(Array $array, Filesystem $fileSystem, string $parentKey = 'origin', $maxLevel = 100)
    {
        $this->level++;

        if($this->level > $maxLevel) {
            return;
        }

        foreach ($array as $key => $value) {

            if (is_array($value)) {

                foreach ($value as $subKey => $item) {
                    if (!is_integer($subKey)) {
                        $origin = $this->escape($key);
                        $target = $this->escape($subKey);
                        $this->addDotNodeIntoFile($fileSystem, $origin, $target);
                    }
                }
                $this->transform($value, $fileSystem, $key);
            }

            else {
                $target = $this->escape($value);

                if(is_integer(array_keys($array)[0])) {
                    $origin = $this->escape($parentKey);
                    $this->addDotNodeIntoFile($fileSystem, $origin, $target);
                }
                else{
                    $origin = $this->escape($key);
                    $this->addDotNodeIntoFile($fileSystem, $origin, $target);
                }

            }


        }

        return true;
    }


    /**
     * @param array $array
     * @return array
     */
    private function compressArray(array $array, string $parent)
    {
        $transformedArray = $array;

        if (gettype(array_keys($array)[0]) === 'integer') {

            $transformedArray = [];

            foreach ($array as $key => $value) {
                //pb ici : comment avoir trois objets du même parent?
                $transformedArray[$parent] = $value;

            }
        }

        return $transformedArray;

    }

    /**
     * @param $object
     * @return bool
     */
    private function isArray($object) {
        return gettype($object) === 'array';
    }

    /**
     * @return string
     */
    private function rapport()
    {
        if($this->nbLines > 0) {
            $this->message = $this->nbLines.' lignes ont été ajoutées.'.$this->level.' niveaux ont été parsés.';
        }
        return $this->message;
    }

//    private function generatePng()
//    {
//
//    }

}


// todo :
//- pouvoir exclure des noeuds (ne prendre en compte que les noeuds spécifiés)
//- enclencher directement la commande pour la génération de png : https://symfony.com/doc/4.1/components/process.html
//- pouvoir ajouter son code par un formulaire
//- déployer