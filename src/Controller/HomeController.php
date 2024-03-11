<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Repository\DestinationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(DestinationRepository $destinationRepository): Response
    {
        $destinations = $destinationRepository->findAll();

        return $this->render('home/index.html.twig', [
            'destinations' => $destinations,
        ]);
    }

    #[Route('/show/{id}', name: 'app_show')]
    public function show(Destination $destination): Response
    {
        return $this->render('home/show.html.twig', [
            'destination' => $destination,
        ]);
    }
}
