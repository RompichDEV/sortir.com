<?php

namespace App\Event;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SortieEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{

    public function __construct(private readonly EtatRepository $etatRepository, private readonly EntityManagerInterface $entityManager, private readonly SortieRepository $sortieRepository)
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'verifierEtatToutesSorties',
            SortieEvent::INSCRIPTION => 'verifierEtatUneSortie',
            SortieEvent::DESISTEMENT => 'verifierEtatUneSortie',

        ];
    }
    //TODO : créer une classe pour ce traitement à injecter dans la méthode du controller + appel méthode qu'elle contient

    /**
     * Met à jour l'état des sorties ouvertes et en corus  en fonction de la date du jour.
     * @param ControllerEvent $event
     * @return void
     */
    public function verifierEtatToutesSorties(ControllerEvent $event): void
    {
        $today = new \DateTime('now');
        /** @var Sortie[] $sortiesOuvertes */
        //TODO: créer méthode plus psécifique pour éviter les 'if' multiples dans le listener.
        $sortiesOuvertes = $this->sortieRepository->findSortiesByState('Ouverte');

//        dd($sortiesEnCours, $sortiesOuvertes);

        //On vérifie si les sorties ouvertes doivent  être 'Clôturée' ou être enregistrée comme 'Activité en cours'
        if(!empty($sortiesOuvertes))
        {
//            dd($sortiesOuvertes);
            foreach ($sortiesOuvertes as $sortie)
            {
                if($today >= $sortie->getDateLimiteInscription())
                {
                    $sortie->setEtat($this->etatRepository->findOneBy(['libelle'=>'Clôturée']));
                    $this->entityManager->persist($sortie);
                    $this->entityManager->flush();
                }
                if($today >= $sortie->getDateHeureDebut())
                {
                    $sortie->setEtat($this->etatRepository->findOneBy(['libelle'=>'Activité en cours']));
                    $this->entityManager->persist($sortie);
                    $this->entityManager->flush();
                }
            }
        }
        /** @var Sortie[] $sortiesCloturees */
        $sortiesCloturees = $this->sortieRepository->findSortiesByState('Clôturée');
        //On vérifie si les sorties clôturées doivent être enregistrées en 'Activité en cours'
        if(!empty($sortiesCloturees))
        {
//            dd($sortiesOuvertes);
            foreach ($sortiesCloturees as $sortie)
            {
                if($today >= $sortie->getDateHeureDebut())
                {
                    $sortie->setEtat($this->etatRepository->findOneBy(['libelle'=>'Activité en cours']));
                    $this->entityManager->persist($sortie);
                    $this->entityManager->flush();
                }
            }
        }
        /** @var Sortie[] $sortiesEnCours */
        $sortiesEnCours = $this->sortieRepository->findSortiesByState('Activité en cours');
        //On vérifie si les sorties en cours doivent être enregistrées comme 'Passées'
        if(!empty($sortiesEnCours))
        {
//            dd($sortiesEnCours);
            foreach ($sortiesEnCours as $sortie)
            {
//                dd($sortie->getDateHeureDebut());
                if(date_diff($sortie->getDateHeureDebut(), $today)->d >=1)
                {
                    $sortie->setEtat($this->etatRepository->findOneBy(['libelle'=>'Passée']));
                    $this->entityManager->persist($sortie);
                    $this->entityManager->flush();
                }
            }
        }
        //TODO : Ajouter état 'Historicisée'
    }



    /**
     * Vérifie et modifie l'état d'une sortie après un inscription/désistement en fonction de le date limite d'inscription et du nombre max de participant.e.s.
     * @param SortieEvent $sortieEvent
     * @return void
     */
    public function verifierEtatUneSortie(
        SortieEvent $sortieEvent):void
    {
        $sortie = $sortieEvent->getSortie();
        $today = new \DateTime('now');
        $nbMaxParticipantsAtteint = count($sortie->getParticipants())== $sortie->getNbInscriptionsMax();
        if($sortie->getEtat()->getLibelle() == 'Ouverte' &&  $nbMaxParticipantsAtteint){
            $sortie->setEtat($this->etatRepository->findOneBy(['libelle'=>'Clôturée']));
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
        }
        if($sortie->getEtat()->getLibelle() == 'Clôturée'
        && !$nbMaxParticipantsAtteint
        && $sortie->getDateLimiteInscription() >= $today)
        {
            $sortie->setEtat($this->etatRepository->findOneBy(['libelle'=>'Ouverte']));
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
        }
    }
}