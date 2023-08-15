<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends BaseController
{
    #[Route('/profil/modifier', name: 'profil_modifier', methods: ['GET','POST'])]
    public function modifierProfil(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $participant = $this->getUser();

        $profilForm = $this->createForm(ProfilType::class, $participant);
        return $this->render('profil/modifier.html.twig', [
            'profilForm' => $profilForm->createView()
        ]);
    }
    #[Route('/profil/details/{id}', name: 'profil_details', requirements: ['id'=>'\d+'], methods: "GET")]
    public function detailsProfil(): Response
    {
        return $this->render('profil/details.html.twig');
    }
}
