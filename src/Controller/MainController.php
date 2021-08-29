<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FilesRepository;
use App\Entity\Files;
use Symfony\Component\HttpFoundation\JsonResponse;

class MainController extends AbstractController
{
    #[Route('/', name: 'index', methods: 'post')]
    public function index(Request $request): Response
    {
        return new JsonResponse('');
    }

    #[Route('/file/create', name: 'create', methods: 'post')]
    public function create(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        date_default_timezone_set('Europe/Moscow');
        $file = new Files();

        $file->setName($request->request->get('type') == 1 ? $request->request->get('name') : $request->request->get('name') . '/');
        $file->setContent($request->request->get('content') || null);
        $file->setType(intval($request->request->get('type'))); //0 - file
        $file->setStatus(1);
        $file->setDateCreate(new \DateTime());
        $file->setDateModify(new \DateTime());
        $file->setPath($request->request->get('path'));

        $hash = hash('sha256', $file->getPath().$file->getName());
        $exists = count($entityManager->getRepository(Files::class)->findBy([
            'hash' => $hash
        ])) == 1;
        $file->setHash($hash);

        /**
         * TODO: Такой файл/папка существует
         */
        $entityManager->persist($file);
        $entityManager->flush();

        return new JsonResponse($file);
    }

    #[Route('/file/{id}', name: 'open', methods: 'post')]
    public function open(Files $file): Response
    {
        $filesrepository = new FilesRepository($this->getDoctrine());
        if($file->getType() == 1)
            return new JsonResponse($file);
        else {
            $currentPath = $file->getPath() . $file->getName();
            $files = $filesrepository->findByFolder($currentPath);

            return new JsonResponse($files);
        }
    }

    #[Route('/file/{id}/update', name: 'update', methods: 'post')]
    public function update(Files $file, Request $request): Response
    {
        $req = $request->request;

        if($file->getType() == 1) {
            $file->setName($req->get('name'));
            $file->setContent($req->get('content'));
            $file->setDateModify(new \DateTime());
        } else if($file->getType() == 2) {
            $file->setName($req->get('name'));
            $file->setDateModify(new \DateTime());
        }

        return new JsonResponse($file);
    }

    #[Route('/file/{id}/delete', name: 'delete', methods: 'post')]
    public function delete(Files $file): Response
    {
        /**
         * TODO: Сделать эксепшен, что файл удален уже
         */
        $entityManager = $this->getDoctrine()->getManager();
        $filesrepository = new FilesRepository($this->getDoctrine());
        $r = null;

        $file->setStatus(2);
        $file->setOldPath($file->getPath());
        $file->setPath('trash/');
        $file->setHashDelete(hash('sha256', (new \DateTime())->format('Y-m-d H:i:s'))); //хэш удаления нужен, чтобы предотвратить коллизии с удалением директории, в которой уже были удалены файлы или директории
        if($file->getType() == 2) 
            $filesrepository->deleteFolder($file->getOldPath().$file->getName(), $file->getHashDelete());
        $entityManager->flush();

        return new JsonResponse($file);
    }

    #[Route('/file/{id}/restore', name: 'restore', methods: 'post')]
    public function restore(Files $file): Response
    {
        /**
         * TODO: Сделать эксепшен, что файл не удален еще
         */
        $entityManager = $this->getDoctrine()->getManager();
        $filesrepository = new FilesRepository($this->getDoctrine());
        $r[] = new Files();
        $test = false;
        
        $file->setStatus(1);
        $exists = count($filesrepository->findBy([
            'hash' => hash('sha256', $file->getOldPath()),
            'status' => '1'
        ])) == 1;
        if($file->getType() == 2) {
            $r = $filesrepository->restoreFolder($file->getOldPath().$file->getName(), $file->getHashDelete());
            if (count($r) > 0 && !$exists) 
                foreach($r as $value) {
                    $temp = $value->getPath(); //Путь каждого файла/директории в выбранной диретории для восставноления
                    $tmp = $file->getPath(); //Абсолютный путь выбранной директории для восстановления

                    $tmp = preg_replace('/\//', '\/', $tmp);
                    $temp = preg_replace('/^'.$tmp.'/', '/', $temp);
                    $value->setHashDeleteNULL();
                    $value->setPath($temp);
                }
        }
        $file->setPath($exists ? $file->getOldPath() : '/');
        $entityManager->flush();

        return new JsonResponse($file);
    }

    #[Route('/file/{id}/permanently', name: 'permanently', methods: 'post')]
    public function permanently(Files $file): Response
    {
        /**
         * TODO: Сделать эксепшен, что файл не удален еще
         */
        $entityManager = $this->getDoctrine()->getManager();
        $filesrepository = new FilesRepository($this->getDoctrine());
        $r = null;

        $file->setStatus(3);
        if($file->getType() == 2) 
            $r = $filesrepository->deleteFolder($file->getOldPath().$file->getName(), null, true);
        $entityManager->flush();

        if ( !empty($r) ){
            return new JsonResponse($file);
        } else {
            return new Response($r);
        }
    }
}

