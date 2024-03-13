<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Repository\DestinationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/destinations')]
class ApiDestinationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private DestinationRepository $destinationRepository;

    public function __construct(EntityManagerInterface $entityManager, DestinationRepository $destinationRepository)
    {
        $this->entityManager = $entityManager;
        $this->destinationRepository = $destinationRepository;
    }

    #[Route('/', name: 'api_destination_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $destinations = $this->destinationRepository->findAll();
        $data = [];

        foreach ($destinations as $destination) {
            $data[] = [
                'id' => $destination->getId(),
                'name' => $destination->getName(),
                'description' => $destination->getDescription(),
                'duration' => $destination->getDuration(),
                'price' => $destination->getPrice(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_destination_show', methods: ['GET'])]
    public function show(Destination $destination): JsonResponse
    {
        $data = [
            'id' => $destination->getId(),
            'name' => $destination->getName(),
            'description' => $destination->getDescription(),
            'duration' => $destination->getDuration(),
            'price' => $destination->getPrice(),
        ];

        return $this->json($data);
    }

    #[Route('/', name: 'api_destination_new', methods: ['POST'])]
    public function new(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = $request->request->all();

        $data['image'] = $request->files->get('image');

        // Validate incoming data
        $errors = $validator->validate($data, new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'description' => new Assert\NotBlank(),
            'price' => new Assert\NotBlank(),
            'duration' => new Assert\NotBlank(),
            'image' => [
                new Image([
                    'maxSize' => '5M', // Adjust the maximum size as needed
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                    ],
                ]),
            ],
        ]));

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Handle file upload
        $imageFile = $request->files->get('image');
        $newFilename = uniqid().'.'.$imageFile->guessExtension();

        try {
            $imageFile->move(
                $this->getParameter('image_directory'),
                $newFilename
            );
        } catch (FileException $e) {
            // Handle file upload error
            return new JsonResponse(['error' => 'Error uploading image'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Create a new Destination entity
        $destination = new Destination();
        $destination->setName($data['name']);
        $destination->setDescription($data['description']);
        $destination->setPrice($data['price']);
        $destination->setDuration($data['duration']);
        $destination->setImage($newFilename);

        $this->entityManager->persist($destination);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Destination created', 'id' => $destination->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_destination_edit', methods: ['POST'])]
    public function edit(Request $request, Destination $destination, ValidatorInterface $validator): JsonResponse
    {
        $data = $request->request->all();
        $data['image'] = $request->files->get('image');

        // Validate incoming data
        $errors = $validator->validate($data, new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'description' => new Assert\NotBlank(),
            'price' => new Assert\NotBlank(),
            'duration' => new Assert\NotBlank(),
            'image' => [
                new Image([
                    'maxSize' => '5M', // Adjust the maximum size as needed
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                    ],
                ]),
            ],
        ]));

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Handle file upload if image is provided
        if (isset($data['image'])) {
            $imageFile = $request->files->get('image');
            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('image_directory'),
                    $newFilename
                );

                // Update the destination image
                $destination->setImage($newFilename);
            } catch (FileException $e) {
                // Handle file upload error
                return new JsonResponse(['error' => 'Error uploading image'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Update other fields of the Destination entity
        $destination->setName($data['name']);
        $destination->setDescription($data['description']);
        $destination->setPrice($data['price']);
        $destination->setDuration($data['duration']);

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Destination updated', 'id' => $destination->getId()]);
    }

    #[Route('/{id}', name: 'api_destination_delete', methods: ['DELETE'])]
    public function delete(Destination $destination): JsonResponse
    {
        $this->entityManager->remove($destination);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Destination deleted']);
    }
}
