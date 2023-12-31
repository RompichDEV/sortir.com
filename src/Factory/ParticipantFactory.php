<?php

namespace App\Factory;

use App\Entity\Participant;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Participant>
 *
 * @method        Participant|Proxy create(array|callable $attributes = [])
 * @method static Participant|Proxy createOne(array $attributes = [])
 * @method static Participant|Proxy find(object|array|mixed $criteria)
 * @method static Participant|Proxy findOrCreate(array $attributes)
 * @method static Participant|Proxy first(string $sortedField = 'id')
 * @method static Participant|Proxy last(string $sortedField = 'id')
 * @method static Participant|Proxy random(array $attributes = [])
 * @method static Participant|Proxy randomOrCreate(array $attributes = [])
 * @method static ParticipantRepository|RepositoryProxy repository()
 * @method static Participant[]|Proxy[] all()
 * @method static Participant[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Participant[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Participant[]|Proxy[] findBy(array $attributes)
 * @method static Participant[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Participant[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class ParticipantFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     *
     */
    protected UserPasswordHasherInterface $hasher;
    protected $campus;
    public function __construct(UserPasswordHasherInterface $hasher, CampusRepository $campusRepository)
    {
        parent::__construct();
        $this->hasher = $hasher;
        $this->campus= $campusRepository->findAll();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        $nb = rand(0,1);
        return [
            'actif' => true,
            'administrateur' => false,
            'email' => self::faker()->email(),
            'nom' => self::faker()->lastName(),
            'motPasse' => 'poney',
            'prenom' => $nb==0 ? self::faker()->unique()->firstNameFemale() : self::faker()->unique()->firstNameMale(),
            'pseudo' => self::faker()->word(),
            'campus' => self::faker()->randomElement($this->campus)
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
             ->afterInstantiate(function(Participant $participant): void {
                 if($participant->getMotPasse()){
                     $participant->setMotPasse(
                         $this->hasher->hashPassword($participant, $participant->getMotPasse())
                     );
                 }
             })
        ;
    }

    protected static function getClass(): string
    {
        return Participant::class;
    }
}
