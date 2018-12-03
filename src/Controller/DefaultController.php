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
//        $parseFile = $this->transformArray($parseFile);
        $parseFile = $this->transform($parseFile);

        $fileSystem = $this->initFile();
        $this->parse($fileSystem, $parseFile);
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
     * @param array $parseFile
     *
     * @return array
     */
    public function transformArray(Array $parseFile)
    {
        $transformedArray = [];

        foreach ($parseFile as $key => $value) {

//            if ($this->isArray($value)) {
//                dump($parseFile);
//                dump($value);die;
//                $transformedArray = $this->transformArray($value);
//                dump($transformedArray);die;
//
//                $transformedArray += $this->compressArray($value, $key);
//                //attention / récursif / mettre une limite
//                dump($transformedArray);
//
//            }
//            else {
//                $transformedArray[$key] = $value;
//            }
        }

        return $transformedArray;
    }

    public function transform($array)
    {
        if (!is_array($array)) {
            dump($array);
            return;
        }
        $helper = [];
        foreach ($array as $key => $value) {
//            dump($value);
            $helper[$this->transform($key)] = is_array($value) ? $this->transform($value) : $this->transform($value);
        }

        return $helper;
    }




public function reformat($data)
    {
        dump($data);
    }


    /**
     * @param $fileSystem
     * @param $parseFile
     * @param int $maxLevel
     * @return int|void
     */
    private function parse($fileSystem, array $parseFile, $maxLevel = 500)
    {
        $this->level++;
        if($this->level > $maxLevel) {
            return;
        }

        foreach ($parseFile as $key => $value) {

            $origin = $this->escape($key);

            if (gettype($value) === 'array')
            {
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
            else {
                $this->addDotNodeIntoFile($fileSystem, $origin, $this->escape($value));
            }
        }

        return $this->level;
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
            $this->message = $this->nbLines.' lignes ont été ajoutées.';
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