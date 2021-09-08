<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FilesRepository;
use App\Entity\Files;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

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
        $body = json_decode($request->getContent());
        $entityManager = $this->getDoctrine()->getManager();
        date_default_timezone_set('Europe/Moscow');

        if(strlen($body->name) < 1) return new JsonResponse([ 'error' => 'Имя не задано']);
        
        $file = new Files();
        $file->setName($body->type == 1 ? $body->name : $body->name . '/');
        $file->setContent($body->content);
        $file->setType(intval($body->type)); //1 - file
        $file->setStatus(1);
        $file->setDateCreate(new \DateTime());
        $file->setDateModify(new \DateTime());
        $file->setPath($body->path);

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
            $files = $filesrepository->findAllFolder($currentPath);

            return new JsonResponse($files);
        }
    }

    #[Route('/file/{id}/update', name: 'update', methods: 'post')]
    public function update(Files $file, Request $request): Response
    {
        $body = json_decode($request->getContent());
        $entityManager = $this->getDoctrine()->getManager();

        if($file->getId() == 1 || $file->getId() == 3) return new Response(403);

        if($file->getType() == 1) {
            $file->setName($body->name);
            $file->setContent($body->content);
            $file->setDateModify(new \DateTime());
        } else if($file->getType() == 2) {
            $file->setName($body->name);
            $file->setDateModify(new \DateTime());
        }
        $entityManager->flush();

        return new JsonResponse($file);
    }

    #[Route('/file/{id}/delete', name: 'delete', methods: 'post')]
    public function delete(Files $file): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $filesrepository = new FilesRepository($this->getDoctrine());

        if($file->getStatus() == 2 || $file->getStatus() == 3) return new JsonResponse([ 'error' => 'Файл удален уже']);
        if($file->getId() == 1 || $file->getId() == 3) return new Response(403);

        $file->setStatus(2);
        $file->setOldPath($file->getPath());
        $file->setPath('trash/');
        $file->setHashDelete(hash('sha256', $file->getDateCreate()->format('Y-m-d H:i:s'))); //хэш удаления нужен, чтобы предотвратить коллизии с удалением директории, в которой уже были удалены файлы или директории
        if($file->getType() == 2) 
            $filesrepository->deleteFolder($file->getOldPath().$file->getName(), $file->getHashDelete());
        $entityManager->flush();

        return new JsonResponse($file);
    }

    #[Route('/file/{id}/restore', name: 'restore', methods: 'post')]
    public function restore(Files $file): Response
    {
        /**
         * TODO: Сделать проверку при восстановление в корень, что такой файл уже существует в системе
         */
        $entityManager = $this->getDoctrine()->getManager();
        $filesrepository = new FilesRepository($this->getDoctrine());
        $r[] = new Files();

        if($file->getStatus() == 1) return new JsonResponse([ 'error' => 'Файл не удален еще']);
        if($file->getId() == 1 || $file->getId() == 3) return new Response(403);
        
        $file->setStatus(1);
        $exists = count($filesrepository->findBy([
            'hash' => hash('sha256', $file->getDateCreate()->format('Y-m-d H:i:s')),
            'status' => '2'
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
        $entityManager = $this->getDoctrine()->getManager();
        $filesrepository = new FilesRepository($this->getDoctrine());
        $r = null;

        if($file->getStatus() == 1) return new JsonResponse([ 'error' => 'Файл не удален еще']);
        if($file->getId() == 1 || $file->getId() == 3) return new Response(403);

        $file->setStatus(3);
        $file->setPath('Null');
        $file->setHashDeleteNULL();
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

