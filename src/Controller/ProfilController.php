<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil/modifier', name: 'profil_modifier', methods: ['GET','POST'])]
    public function modifierProfil(Request $request,
                                   EntityManagerInterface $entityManager,
                                   UserPasswordHasherInterface $hasher): Response
    {
        /** @var Participant $participant */
        $participant = $this->getUser();
        //Créer un autre utilisateur ou

        $profilForm = $this->createForm(ProfilType::class, $participant);
        $profilForm->handleRequest($request);
        if($profilForm->isSubmitted() && $profilForm->isValid()){

            $motPasseClair = $profilForm['motPasseClair']->getData();
            //SI l'utilsateur.ice a saisi un nouveau de mot de passe, on le vérifie et on modifie l'attribut mot de passe s'il est valide.
            if(!is_null($motPasseClair) && !empty(trim($motPasseClair)))
            {
                $participant->setMotPasse(
                    $hasher->hashPassword($participant, $motPasseClair)
                );
            }
            if(!is_null($motPasseClair) && empty($motPasseClair)){
                $this->addFlash('warning', 'Le mot de passe ne peut être une chaîne vide !');
                return $this->redirectToRoute('profil_modifier');
            }

           $entityManager->persist($participant);
           $entityManager->flush();

            $this->addFlash('success', 'Modifications enregistrées avec succès !');
            return $this->redirectToRoute('main_accueil');
        }
        $entityManager->refresh($participant);
        return $this->render('profil/modifier.html.twig', [
            'profilForm'=>$profilForm->createView()
        ]);
    }
    #[Route('/profil/details/{id}', name: 'profil_details', requirements: ['id'=>'\d+'], methods: "GET")]
    public function detailsProfil(int $id,
                                  ParticipantRepository $participantRepository): Response
    {
        $profilConsulte = $participantRepository->find($id);
        return $this->render('profil/details.html.twig', [
            'profilConsulte'=>$profilConsulte
        ]);
    }
}
