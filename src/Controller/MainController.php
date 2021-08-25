<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FilesRepository;
use App\Repository\UsersRepository;
use App\Entity\Files;
use App\Entity\Status;
use App\Entity\Type;
use App\Entity\Users;
use DateTimeInterface;

class MainController extends AbstractController
{
    #[Route('/test', name: 'index', methods: 'post')]
    public function index(Request $request): Response
    {
        return new Response('');
    }

    #[Route('/file/create', name: 'create', methods: 'post')]
    public function create(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        date_default_timezone_set('Europe/Moscow');
        $file = new Files();

        $file->setName($request->request->get('name'));
        $file->setContent($request->request->get('content'));
        $file->setType(0); //0 - file
        $file->setStatus(0);
        $file->setDateCreate(new \DateTime());
        $file->setDateModify(new \DateTime());
        $file->setPath($request->request->get('path'));

        $entityManager->persist($file);
        $entityManager->flush();

        return new Response('Saved new product with id '.$file->getId());
    }
}
