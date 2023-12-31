<?php

namespace App\Service;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class GestionnaireEtat
{
    public function __construct(private readonly SortieRepository $sortieRepository,
                                private readonly EtatRepository $etatRepository,
                                private readonly EntityManagerInterface $entityManager)
    {
    }
    public function gererEtats(): int
    {
        $compteur = 0;
        /** @var Sortie[] $sortiesOuvertes */
        $sortiesOuvertes = $this->sortieRepository->findSortiesByStateAndDate('Ouverte');

        //On vérifie si les sorties ouvertes doivent  être 'Clôturée' ou être enregistrée comme 'Activité en cours'
        if(!empty($sortiesOuvertes))
        {
            $etat=$this->etatRepository->findOneBy(['libelle'=>'Clôturée']);
            foreach ($sortiesOuvertes as $sortie)
            {
                    $sortie->setEtat($etat);
                    $this->entityManager->persist($sortie);
                    $compteur+=1;
            }
            $this->entityManager->flush();
        }
        /** @var Sortie[] $sortiesCloturees */
        $sortiesCloturees = $this->sortieRepository->findSortiesByStateAndDate('Clôturée');
        //On vérifie si les sorties clôturées doivent être enregistrées en 'Activité en cours'
        if(!empty($sortiesCloturees))
        {
            $etat=$this->etatRepository->findOneBy(['libelle'=>'Activité en cours']);
            foreach ($sortiesCloturees as $sortie)
            {
                    $sortie->setEtat($etat);
                    $this->entityManager->persist($sortie);
                    $compteur+=1;
            }
            $this->entityManager->flush();
        }
        /** @var Sortie[] $sortiesEnCours */
        $sortiesEnCours = $this->sortieRepository->findSortiesByStateAndDate('Activité en cours');
        //On vérifie si les sorties en cours doivent être enregistrées comme 'Passées'
        if(!empty($sortiesEnCours))
        {
            $etat=$this->etatRepository->findOneBy(['libelle'=>'Passée']);
            foreach ($sortiesEnCours as $sortie)
            {
                    $sortie->setEtat($etat);
                    $this->entityManager->persist($sortie);
                    $compteur+=1;

            }
            $this->entityManager->flush();
        }
        /** @var Sortie[] $sortiesPasseesAnnulees */
        $sortiesPassees = $this->sortieRepository->findSortiesByStateAndDate('Passée');
        $sortiesAnnulees = $this->sortieRepository->findSortiesByStateAndDate('Annulée');
        $sortiesPasseesAnnulees = array_merge($sortiesPassees, $sortiesAnnulees);
        //On récupère  les sorties passées ou annulées dont la date de début prévue est ancienne d'au moins un mois (31 jours) pour les passer en état 'Archivée'
        if(!empty($sortiesPasseesAnnulees))
        {
            $etat=$this->etatRepository->findOneBy(['libelle'=>'Archivée']);
            foreach ($sortiesPasseesAnnulees as $sortie)
            {
                $sortie->setEtat($etat);
                $this->entityManager->persist($sortie);
                $compteur+=1;
            }
            $this->entityManager->flush();
        }
        return $compteur;
    }
}